<?php namespace App\Repositories;

use App\Models\Tag as Tag;
use App\Models\ConfessionLog as ConfessionLog;
use App\Repositories\BaseRepository;

class ConfessionsRepository extends BaseRepository {

    private $_pageToken = '';

    public function model()
    {
        return 'App\Models\Confession';
    }

    public function create(array $data, $categories = [])
    {
        $confession = $this->model->create($data);

        $this->syncTags($confession);
        $this->syncCategories($confession, $categories);

        return $confession->save();
    }

    public function update($id, array $data, $categories = [])
    {
        $confession = $this->model->find($id);
        if (!$confession) {
            throw new \Exception("Model #{$id} is not found");
        }

        // Switch status
        $switched = false;
        if ($data['status'] != $confession->status) {
            $this->switchStatus($confession, $data['status'], false);
            $switched = true;
        }

        $confession->fill($data);

        // Update Facebook if it's featured or approved
        if (!$switched && ($confession->status == 'Featured' || $confession->status == 'Approved'))
            $this->postToFacebook($confession);

        $this->syncTags($confession);
        $this->syncCategories($confession, $categories);

        return $confession->save();
    }

    public function delete($id)
    {
        $confession = $this->model->find($id);
        if (!$confession) {
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

    public function switchStatus($confession, $new, $save = true)
    {
        switch ($new) {
            case 'Featured':
            case 'Approved':
                if (!$confession->fb_post_id) {
                    $confession->fb_post_id = $this->postToFacebook($confession);
                }
                break;
            case 'Pending':
            case 'Rejected':
                if ($confession->fb_post_id) {
                    $this->deleteFromFacebook($confession);
                }
                break;
        }
        $old = $confession->status;
        $confession->status = $new;

        if (\Auth::check()) {
            $user = \Auth::user()->getAuthIdentifier();
            $log = new ConfessionLog([
                'status_before' => $old,
                'status_after' => $new,
                'changed_by_user' => $user,
            ]);
            $confession->logs()->save($log);
        }

        if ($save)
            return $confession->save();
        else
            return true;
    }

    public function getPageToken()
    {
        if (!$this->_pageToken) {
            $profile = \Auth::user()->profiles()->where('provider_name', '=', 'facebook')->get();
            if (count($profile) !== 1) {
                return false;
            }
            $this->_pageToken = $profile[0]->page_token;
        }
        return $this->_pageToken;
    }

    protected function postToFacebook($confession)
    {
        if ($confession->images) {
            if ($confession->fb_post_id) {
                $endpoint = '/' . $confession->fb_post_id;
            } else {
                $endpoint = '/' . env('FACEBOOK_PAGE_ID', '') . '/photos';
            }

            $response = \Facebook::post($endpoint, [
                'message' => $confession->content . "\n\n" . url('/#!/confession/' . $confession->confession_id),
                'url'  => $confession->images,
            ], $this->getPageToken())->getGraphObject();

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

            if ($confession->confession_id % 5 == 0) { // yup, random
                $message = $confession->content . "\n-\n" . 'Submit your own confessions at: ' . url('/');
            } else {
                $message = $confession->content;
            }

            $response = \Facebook::post($endpoint, [
                'message' => $message,
                // 'link' => url('/#!/confession/' . $confession->confession_id)
            ], $this->getPageToken())->getGraphObject();

            if ($confession->fb_post_id) {
                return $confession->fb_post_id;
            } else {
                return explode('_', $response['id'])[1];
            }
        }
    }

    protected function deleteFromFacebook($confession)
    {
        if ($confession->images) {
            \Facebook::delete('/' . $confession->fb_post_id, [], $this->getPageToken());
        } else {
            \Facebook::delete('/' . env('FACEBOOK_PAGE_ID', '') . '_' . $confession->fb_post_id, [], $this->getPageToken());
        }
        $confession->fb_post_id = '';
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

    protected function syncCategories(&$confession, $categories)
    {
        $confession->categories()->sync($categories);
        return $confession;
    }

    protected function getTagNamesFromContent($content)
    {
        preg_match_all('/(#\w+)/', $content, $matches);
        return array_unique(array_shift($matches));
    }

}
