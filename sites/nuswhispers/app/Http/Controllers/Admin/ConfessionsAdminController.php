<?php namespace App\Http\Controllers\Admin;

use App\Models\Category as Category;
use App\Models\Confession as Confession;
use App\Repositories\ConfessionsRepository;

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
            $query = $query->has('categories', '=', intval(\Input::get('category')));
        }

        if (\Input::get('q'))
        {
            $search = stripslashes(\Input::get('q'));
            $query = $query->where('content', 'LIKE', "%$search%");
        }

        if ($status != 'all')
        {
            $query = $query->where('status', '=', ucfirst($status));
        }

        $confessions = $query->paginate(10);

        return view('admin.confessions.index', [
            'confessions' => $confessions,
            'categoryOptions' => array_merge(array('All Categories' => 0), Category::orderBy('confession_category', 'asc')->lists('confession_category_id', 'confession_category')),
            'hasPageToken' => (bool)$this->confessionsRepo->getPageToken(),
        ]);
    }

    public function getEdit($id)
    {
        $confession = Confession::findOrFail($id);

        return view('admin.confessions.edit', [
            'confession' => $confession,
        ]);
    }

    public function postEdit($id)
    {
        $validationRules = [
            'content' => 'required',
            'categories' => 'array',
            'status' => 'in:Featured,Pending,Approved,Rejected'
        ];

        $validator = \Validator::make(\Input::all(), $validationRules);
        if ($validator->fails()) {
            return \Redirect::back()->withErrors($validator);
        }

        try {
            $res = $this->confessionsRepo->update($id, [
                'content' => \Input::get('content'),
                'status' => \Input::get('status'),
            ], \Input::get('categories'));

            return \Redirect::back()->withMessage('Confession successfully updated.')
                ->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Failed updating confession: ' . $e->getMessage())
                ->with('alert-class', 'alert-danger');
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
