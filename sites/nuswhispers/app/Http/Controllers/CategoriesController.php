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
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Get the category JSON by a given category_id
	 *
 * @param  int $category_id
 * @return json {"success": true or false, "data": {"category": category}};
	 */
	public function show($category_id)
	{
		$category = Category::find($category_id);
		if ($category == NULL){
			return \Response::json(array("success" => false));
		}
		return \Response::json(["success" => true, "data" => array("category" => $category)]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
