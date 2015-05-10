<?php namespace App\Http\Controllers;

use App\Models\Category as Category;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class CategoriesController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $json = \Cache::rememberForever('categories_json', function()
        {
            return ['data' => ['categories' => Category::categoryAsc()->get()]];
        });
        return \Response::json($json);
    }


    /**
     * Get the category JSON by a given category_id
     * route: api/categories/<category_id>
     * @param  int $category_id
     * @return json {"success": true or false, "data": {"category": category}};
     */
    public function show($categoryd)
    {
        $category = Category::find($categoryId);
        if ($category == NULL) {
            return \Response::json(array("success" => false));
        }
        return \Response::json(["success" => true, "data" => array("category" => $category)]);
    }

}
