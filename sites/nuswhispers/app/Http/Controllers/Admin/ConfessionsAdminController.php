<?php namespace App\Http\Controllers\Admin;

use App\Models\Category as Category;
use App\Models\Confession as Confession;
use App\Models\ModeratorComment as ModeratorComment;
use App\Repositories\ConfessionsRepository;
use Carbon\Carbon;

use Illuminate\Http\Request;

class ConfessionsAdminController extends AdminController {

    protected $confessionsRepo;

    public function __construct(ConfessionsRepository $confessionsRepo)
    {
        $this->confessionsRepo = $confessionsRepo;
        return parent::__construct();
    }

    public function getIndex($status = 'Pending')
    {
        $status = ucfirst($status);
        if ($status != 'Pending')
        {
            $query = Confession::orderBy('created_at', 'desc');
        }
        else
        {
            $query = Confession::orderBy('created_at', 'asc');
        }

        if (\Input::get('category'))
        {
            $query->whereHas('categories', function($query) {
                $query->where('confession_categories.confession_category_id', '=', intval(\Input::get('category')));
            });
        }

        if (\Input::get('q'))
        {
            $search = stripslashes(\Input::get('q'));
            $query->where('content', 'LIKE', "%$search%");
        }

        if (\Input::get('start') && \Input::get('end'))
        {
            $start = Carbon::createFromFormat('U', strtotime(\Input::get('start')))->startOfDay();
            $end = Carbon::createFromFormat('U', strtotime(\Input::get('end')))->startOfDay();

            if ($start > $end)
            {
                return \Redirect::back()->withMessage('Start date cannot be later than end date.')
                    ->with('alert-class', 'alert-danger');
            }

            $query->where('created_at', '>=', $start->toDateTimeString());
            $query->where('created_at', '<', $end->toDateTimeString());
        }

        if ($status != 'All')
        {
            $query->where('status', '=', ucfirst($status));
        }

        $confessions = $query->with('moderatorComments')->paginate(10);

        return view('admin.confessions.index', [
            'confessions' => $confessions,
            'categoryOptions' => array_merge(array('All Categories' => 0), Category::orderBy('confession_category', 'asc')->lists('confession_category_id', 'confession_category')),
            'hasPageToken' => (bool)$this->confessionsRepo->getPageToken(),
        ]);
    }

    public function getEdit($id)
    {
        $confession = Confession::with('moderatorComments')->findOrFail($id);

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
                'content' => \Input::get('comment'),
                'user_id' => \Auth::user()->getAuthIdentifier(),
                'created_at' => new \DateTime()
            ]);

            $confession = Confession::with('moderatorComments')->findOrFail($id);
            $confession->moderatorComments()->save($comment);

            return \Redirect::back()->withMessage('Comment successfully added.')
                    ->with('alert-class', 'alert-success');
        } else {
            $validationRules = [
                'content' => 'required',
                'categories' => 'array',
                'status' => 'in:Featured,Pending,Approved,Rejected'
            ];

            $validator = \Validator::make(\Input::all(), $validationRules);
            if ($validator->fails()) {
                return \Redirect::back()->withInput()->withErrors($validator);
            }

            try {
                $data = [
                    'content' => \Input::get('content'),
                    'status' => \Input::get('status'),
                    'images' => \Input::get('images')
                ];

                if (env('MANUAL_MODE', false) && \Input::get('fb_post_id')) {
                    $data['fb_post_id'] = \Input::get('fb_post_id');
                }

                $res = $this->confessionsRepo->update($id, $data, \Input::get('categories'));

                return \Redirect::back()->withMessage('Confession successfully updated.')
                    ->with('alert-class', 'alert-success');
            } catch (\Exception $e) {
                return \Redirect::back()->withMessage('Failed updating confession: ' . $e->getMessage())
                    ->with('alert-class', 'alert-danger');
            }
        }

    }

    public function getApprove($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (!$this->confessionsRepo->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            $this->confessionsRepo->switchStatus($confession, 'Approved');

            return \Redirect::back()->withMessage('Confession successfully approved and posted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error approving confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getFeature($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (!$this->confessionsRepo->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            $this->confessionsRepo->switchStatus($confession, 'Featured');

            return \Redirect::back()->withMessage('Confession successfully featured.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error featuring confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getUnfeature($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (!$this->confessionsRepo->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            $this->confessionsRepo->switchStatus($confession, 'Approved');

            return \Redirect::back()->withMessage('Confession successfully removed from featured.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error removing confession from featured: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getReject($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (!$this->confessionsRepo->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            $this->confessionsRepo->switchStatus($confession, 'Rejected');

            return \Redirect::back()->withMessage('Confession successfully rejected.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error rejecting confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getDelete($id)
    {
        try {
            $this->confessionsRepo->delete($id);

            return \Redirect::back()->withMessage('Confession successfully deleted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error deleting confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

}
