<?php namespace App\Http\Controllers\Admin;

use App\Models\Category as Category;
use App\Models\Confession as Confession;

use Illuminate\Http\Request;

class ConfessionsAdminController extends AdminController {

    private $_pageToken = '';

    public function getIndex($status = 'Pending')
    {
        $query = Confession::orderBy('created_at', 'desc');

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

        return view('admin.confessions.index', array(
            'confessions' => $confessions,
            'categoryOptions' => array_merge(array('All Categories' => 0), Category::orderBy('confession_category', 'asc')->lists('confession_category_id', 'confession_category')),
            'hasPageToken' => (bool)$this->getPageToken(),
        ));
    }

    public function getEdit($id)
    {

    }

    public function postEdit()
    {

    }

    public function getApprove($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (!$this->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            // Post confession to Facebook
            $confession->fb_post_id = $this->postToFacebook($confession);

            // @TODO: Log the approval

            $confession->status = 'Approved';
            $confession->save();

            return \Redirect::back()->withMessage('Confession successfully approved and posted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error approving confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getFeature($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (!$this->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            // Post confession to Facebook
            if (!$confession->fb_post_id) {
                $confession->fb_post_id = $this->postToFacebook($confession);
            }

            // @TODO: Log the approval

            $confession->status = 'Featured';
            $confession->save();

            return \Redirect::back()->withMessage('Confession successfully featured.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error featuring confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getUnfeature($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (!$this->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            // @TODO: Log the approval

            $confession->status = 'Approved';
            $confession->save();

            return \Redirect::back()->withMessage('Confession successfully removed from featured.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error removing confession from featured: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getReject($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (!$this->getPageToken()) {
            return \Redirect::back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            if ($confession->fb_post_id) {
                $this->deleteFromFacebook($confession->fb_post_id, (bool)$confession->images);
            }

            // @TODO: Log the approval

            $confession->fb_post_id = '';
            $confession->status = 'Rejected';
            $confession->save();

            return \Redirect::back()->withMessage('Confession successfully rejected.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error rejecting confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getDelete($id)
    {
        $confession = Confession::findOrFail($id);

        try {
            if ($confession->fb_post_id) {
                $this->deleteFromFacebook($confession->fb_post_id, (bool)$confession->images);
            }

            // @TODO: Log the approval

            $confession->delete();

            return \Redirect::back()->withMessage('Confession successfully deleted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error deleting confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    protected function getPageToken()
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
            $response = \Facebook::post('/' . env('FACEBOOK_PAGE_ID', '') . '/photos', [
                'message' => $confession->content . "\n\n" . url('/#!/confession/' . $confession->confession_id),
                'url'  => $confession->images,
            ], $this->getPageToken())->getGraphObject();
            return $response['id'];
        } else {
            $response = \Facebook::post('/' . env('FACEBOOK_PAGE_ID', '') . '/feed', [
                'message' => $confession->content,
                'link' => url('/#!/confession/' . $confession->confession_id)
            ], $this->getPageToken())->getGraphObject();

            return explode('_', $response['id'])[1];
        }
    }

    protected function deleteFromFacebook($id, $hasImage = false)
    {
        if ($hasImage) {
            \Facebook::delete('/' . $id, [], $this->getPageToken());
        } else {
            \Facebook::delete('/' . env('FACEBOOK_PAGE_ID', '') . '_' . $id, [], $this->getPageToken());
        }
    }

}
