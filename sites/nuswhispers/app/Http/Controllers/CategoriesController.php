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
		return \Response::json(array("data" => array("categories" => Category::categoryAsc()->get())));
	}


	/**
	 * Get the category JSON by a given category_id
	 * route: api/categories/<category_id>
	 * @param  int $category_id
	 * @return json {"success": true or false, "data": {"category": category}};
	 */
	public function show($category_id)
	{
		$category = Category::find($category_id);
		if ($category == NULL) {
			return \Response::json(array("success" => false));
		}
		return \Response::json(["success" => true, "data" => array("category" => $category)]);
	}

}
