<?php namespace App\Http\Controllers\Admin;

use App\Models\Category as Category;
use App\Models\Confession as Confession;

use Illuminate\Http\Request;

class ConfessionsAdminController extends AdminController {

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
            'categoryOptions' => array_merge(array('All Categories' => 0), Category::orderBy('confession_category', 'asc')->lists('confession_category_id', 'confession_category'))
        ));
    }

    public function getEdit($id)
    {

    }

    public function postApprove()
    {

    }

    public function postEdit()
    {

    }

    public function postDelete()
    {

    }

}
