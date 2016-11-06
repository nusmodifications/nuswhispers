<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category as Category;
use App\Models\Confession as Confession;
use App\Models\ModeratorComment as ModeratorComment;
use App\Repositories\ConfessionsRepository;
use Carbon\Carbon;

class ConfessionsAdminController extends AdminController
{
    protected $confessionsRepo;

    public function __construct(ConfessionsRepository $confessionsRepo)
    {
        $this->confessionsRepo = $confessionsRepo;

        return parent::__construct();
    }

    public function getIndex($status = 'Pending')
    {
        $status = ucfirst($status);
        if ($status != 'Pending') {
            $query = Confession::orderBy('created_at', 'desc');
        } else {
            $query = Confession::orderBy('created_at', 'asc');
        }

        if (\Input::get('category')) {
            $query->whereHas('categories', function ($query) {
                $query->where('confession_categories.confession_category_id', '=', intval(\Input::get('category')));
            });
        }

        if (\Input::get('q')) {
            $search = stripslashes(\Input::get('q'));
            $query->where(function ($q) use ($search) {
                $q->where('content', 'LIKE', "%$search%");
                $q->orWhere('confession_id', (int) $search);
            });
        }

        if (\Input::get('start') && \Input::get('end')) {
            $start = Carbon::createFromFormat('U', strtotime(\Input::get('start')))->startOfDay();
            $end = Carbon::createFromFormat('U', strtotime(\Input::get('end')))->startOfDay();

            if ($start > $end) {
                return \Redirect::back()->withMessage('Start date cannot be later than end date.')
                    ->with('alert-class', 'alert-danger');
            }

            $query->where('created_at', '>=', $start->toDateTimeString());
            $query->where('created_at', '<', $end->toDateTimeString());
        }

        if ($status != 'All') {
            $query->where('status', '=', ucfirst($status));
        }

        $confessions = $query->with('moderatorComments')->paginate(10);
        $categories = Category::orderBy('confession_category', 'asc')->pluck('confession_category_id', 'confession_category')->all();

        return view('admin.confessions.index', [
            'confessions'     => $confessions,
            'categoryOptions' => array_merge(['All Categories' => 0], $categories),
            'hasPageToken'    => (bool) $this->confessionsRepo->getPageToken(),
        ]);
    }

    public function getEdit($id)
    {
        $confession = Confession::with('moderatorComments', 'queue')->findOrFail($id);

        return view('admin.confessions.edit', [
            'confession' => $confession,
        ]);
    }

    public function postEdit($id)
    {
        if (\Input::get('action') == 'Post Comment') {
            $validationRules = [
                'comment' => 'required',
            ];

            $validator = \Validator::make(\Input::all(), $validationRules);
            if ($validator->fails()) {
                return \Redirect::back()->withInput()->withErrors($validator);
            }

            $comment = new ModeratorComment([
                'content'    => \Input::get('comment'),
                'user_id'    => \Auth::user()->getAuthIdentifier(),
                'created_at' => new \DateTime(),
            ]);

            $confession = Confession::with('moderatorComments')->findOrFail($id);
            $confession->moderatorComments()->save($comment);

            return \Redirect::back()->withMessage('Comment successfully added.')
                    ->with('alert-class', 'alert-success');
        } else {
            $validationRules = [
                'content'    => 'required',
                'categories' => 'array',
                'status'     => 'in:Featured,Pending,Approved,Rejected',
            ];

            $validator = \Validator::make(\Input::all(), $validationRules);
            if ($validator->fails()) {
                return \Redirect::back()->withInput()->withErrors($validator);
            }

            try {
                $data = [
                    'content'  => \Input::get('content'),
                    'status'   => \Input::get('status'),
                    'images'   => \Input::get('images'),
                    'schedule' => \Input::get('schedule'),
                ];

                if (env('MANUAL_MODE', false) && \Input::get('fb_post_id')) {
                    $data['fb_post_id'] = \Input::get('fb_post_id');
                }

                $res = $this->confessionsRepo->update($id, $data, \Input::get('categories'));

                return \Redirect::back()->withMessage('Confession successfully updated.')
                    ->with('alert-class', 'alert-success');
            } catch (\Exception $e) {
                return \Redirect::back()->withMessage('Failed updating confession: '.$e->getMessage())
                    ->with('alert-class', 'alert-danger');
            }
        }
    }

    public function getApprove($id, $hours = 0)
    {
        return $this->switchOrScheduleConfession($id, 'Approved', intval($hours));
    }

    public function getFeature($id, $hours = 0)
    {
        return $this->switchOrScheduleConfession($id, 'Featured', intval($hours));
    }

    protected function switchOrScheduleConfession($id, $status, $hours)
    {
        $confession = Confession::findOrFail($id);

        if (! $this->confessionsRepo->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            if ($hours > 0) {
                $this->confessionsRepo->schedule($confession, $status, Carbon::now()->addHours($hours));
                $this->confessionsRepo->switchStatus($confession, 'Scheduled');

                return \Redirect::back()->withMessage('Confession has been scheduled to be '.strtolower($status).' in '.$hours.' hour(s).')->with('alert-class', 'alert-success');
            } else {
                $this->confessionsRepo->switchStatus($confession, $status);

                return \Redirect::back()->withMessage('Confession successfully '.strtolower($status).' and posted.')->with('alert-class', 'alert-success');
            }
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error switching status of confession: '.$e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getUnfeature($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (! $this->confessionsRepo->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            $this->confessionsRepo->switchStatus($confession, 'Approved');

            return \Redirect::back()->withMessage('Confession successfully removed from featured.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error removing confession from featured: '.$e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getReject($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (! $this->confessionsRepo->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            $this->confessionsRepo->switchStatus($confession, 'Rejected');

            return \Redirect::back()->withMessage('Confession successfully rejected.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error rejecting confession: '.$e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getDelete($id)
    {
        try {
            $this->confessionsRepo->delete($id);

            return \Redirect::back()->withMessage('Confession successfully deleted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error deleting confession: '.$e->getMessage())->with('alert-class', 'alert-danger');
        }
    }
}
