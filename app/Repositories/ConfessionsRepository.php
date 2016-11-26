<?php

namespace App\Repositories;

use App\Models\ConfessionQueue;
use App\Models\Tag;
use Carbon\Carbon;

class ConfessionsRepository extends BaseRepository
{
    private $_pageToken = '';

    public function create(array $data, $categories = [])
    {
        $confession = $this->model->create($data);
        $confession->status_updated_at = new \DateTime();

        $this->syncTags($confession);
        $this->syncCategories($confession, $categories);

        return $confession->save();
    }

    public function delete($id)
    {
        $confession = $this->model->find($id);
        if (! $confession) {
            throw new \Exception("Model #{$id} is not found");
        }

        try {
            if ($confession->fb_post_id) {
                $this->deleteFromFacebook($confession);
            }
        } catch (\Exception $e) {
            $confession->delete();
        }

        return $confession->delete();
    }

    public function getPageToken($user = null)
    {
        if (\Auth::check()) {
            $user = \Auth::user();
        }

        if (! $this->_pageToken) {
            $profile = $user->profiles()->where('provider_name', '=', 'facebook')->get();
            if (count($profile) !== 1) {
                return false;
            }
            $this->_pageToken = $profile[0]->page_token;
        }

        return $this->_pageToken;
    }

    public function model()
    {
        return 'App\Models\Confession';
    }

    public function schedule($confession, $status, $time)
    {
        $time = Carbon::createFromFormat('U', strtotime($time));

        // Delete any existing queues
        if ($confession->queue()) {
            $confession->queue()->delete();
        }

        $queue = new ConfessionQueue([
            'status_after' => $status,
            'update_status_at' => $time,
        ]);
        $confession->queue()->save($queue);
    }

    public function switchStatus($confession, $new, $save = true)
    {
        if (\Auth::check()) {
            $user = \Auth::user();
        } else {
            // Get latest log's owner (for scheduled)
            $logs = $confession->logs()
                ->orderBy('created_on', 'DESC')
                ->take(1)
                ->with('user')
                ->get();
            if ($logs) {
                $user = $logs->get(0)->user()->with('profiles')->get()->get(0);
            }
        }
        if ($user) {
            switch ($new) {
                case 'Featured':
                case 'Approved':
                    if (! $confession->fb_post_id) {
                        $confession->fb_post_id = $this->postToFacebook($confession, $user);
                    }
                    break;
                case 'Pending':
                case 'Rejected':
                    if ($confession->fb_post_id) {
                        $this->deleteFromFacebook($confession, $user);
                    }
                    $this->unschedule($confession);
                    break;
            }
            $old = $confession->status;
            $confession->status = $new;
            $confession->status_updated_at = new \DateTime();
        }

        if ($save) {
            return $confession->save();
        } else {
            return true;
        }
    }

    public function unschedule($confession)
    {
        // Delete any existing queues
        if ($confession->queue()) {
            $confession->queue()->delete();
        }
    }

    public function update($id, array $data, $categories = [])
    {
        $confession = $this->model->with('queue')->find($id);
        if (! $confession) {
            throw new \Exception("Model #{$id} is not found");
        }

        // Remove scheduling for 'Pending' and 'Rejected'
        if ($data['status'] == 'Pending' || $data['status'] == 'Rejected') {
            $data['schedule'] = '';
            // Delete any existing queues
            if ($confession->queue()) {
                $confession->queue()->delete();
            }
        }

        // Check if we need to schedule the confession
        if ($data['schedule'] != '') {
            $this->schedule($confession, $data['status'], $data['schedule']);
            $data['status'] = 'Scheduled';
        }

        // Switch status
        $switched = false;
        if ($data['status'] != $confession->status) {
            $this->switchStatus($confession, $data['status'], false);
            $switched = true;
        }

        $confession->fill($data);

        // Update Facebook if it's featured or approved
        if (! $switched && ($confession->status == 'Featured' || $confession->status == 'Approved')) {
            $this->postToFacebook($confession);
        }

        $this->syncTags($confession);
        $this->syncCategories($confession, $categories);

        return $confession->save();
    }

    protected function deleteFromFacebook($confession, $user = null)
    {
        if (env('MANUAL_MODE', false)) {
            $confession->fb_post_id = '';

            return 0;
        }

        try {
            if ($confession->images) {
                \Facebook::delete('/' . $confession->fb_post_id, [], $this->getPageToken($user));
            } else {
                \Facebook::delete('/' . env('FACEBOOK_PAGE_ID', '') . '_' . $confession->fb_post_id, [], $this->getPageToken($user));
            }
        } catch (\Exception $e) {
        }
        $confession->fb_post_id = '';
    }

    protected function getTagNamesFromContent($content)
    {
        preg_match_all('/(#\w+)/', $content, $matches);

        return array_unique(array_shift($matches));
    }

    protected function postToFacebook($confession, $user = null)
    {
        if (env('MANUAL_MODE', false)) {
            return 0;
        }

        if ($confession->images) {
            if ($confession->fb_post_id) {
                $endpoint = '/' . $confession->fb_post_id;
            } else {
                $endpoint = '/' . env('FACEBOOK_PAGE_ID', '') . '/photos';
            }

            $response = \Facebook::post($endpoint, [
                'message' => $confession->getFacebookMessage(),
                'url' => $confession->images,
            ], $this->getPageToken($user))->getGraphObject();

            if ($confession->fb_post_id) {
                return $confession->fb_post_id;
            } else {
                return $response['id'];
            }
        } else {
            if ($confession->fb_post_id) {
                $endpoint = '/' . env('FACEBOOK_PAGE_ID', '') . '_' . $confession->fb_post_id;
            } else {
                $endpoint = '/' . env('FACEBOOK_PAGE_ID', '') . '/feed';
            }

            $response = \Facebook::post($endpoint, [
                'message' => $confession->getFacebookMessage(),
                // 'link' => url('/#!/confession/' . $confession->confession_id)
            ], $this->getPageToken($user))->getGraphObject();

            if ($confession->fb_post_id) {
                return $confession->fb_post_id;
            } else {
                return explode('_', $response['id'])[1];
            }
        }
    }

    protected function syncCategories(&$confession, $categories)
    {
        if ($categories == null) {
            $categories = [];
        }

        $confession->categories()->sync($categories);

        return $confession;
    }

    protected function syncTags(&$confession)
    {
        $tagNames = $this->getTagNamesFromContent($confession->content);
        $tagsToSync = [];
        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(['confession_tag' => $tagName]);
            $tagsToSync[] = $tag->confession_tag_id;
        }
        $confession->tags()->sync($tagsToSync);

        return $confession;
    }
}
