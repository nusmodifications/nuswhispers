<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ConfessionsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return \Response::json(\Confession::get());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$validationRules = array(
			'content' => 'required',
			'image' => 'image',
			'categories' => 'required|array'
		);

		$validator = \Validator::make(\Input::all(), $validationRules);

		if ($validator->fails()) {
			return \Response::json(array('success' => false));
		}

		$newConfession = new \Confession;

		$newConfession->content = \Input::get('content');
		$newConfession->images = \Input::get('image');
		$newConfession->save();

		// get all tags in content
		preg_match_all('/(#\w+)/', $newConfession->content, $matches);
		$tagNames = array_shift($matches); // get full pattern matches from match result
		foreach ($tagNames as $tagName) {
			$confessionTag = \Tag::firstOrNew(array('confession_tag' => $tagName));
			$confessionTag->save();
			$newConfession->tags()->attach($confessionTag->confession_tag_id);
		}

		$newConfession->categories()->attach(\Input::get('categories'));
		$newConfession->save();

		return \Response::json(array('success' => true));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
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
