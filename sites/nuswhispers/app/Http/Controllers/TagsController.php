<?php namespace App\Http\Controllers;

use App\Models\Tag as Tag;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class TagsController extends Controller {

	/**
	 * Get all the existing tags JSON in sorted order
	 * method: get
	 * route: api/tags
	 * @return json {"data": {tags": [tag1, tag2, ...]}}
	 */
	public function index()
	{
		return \Response::json(array("data" => array("tags" => Tag::get())));
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
	 * Get the top n tags JSON sorted by number of associated posts 
	 * method: get
	 * route: api/tags/top/<num> (if not clashes with api below)
	 * @param int $num
	 * @return json {"data": {"tags": [tag1, tag2, ...]}}
	 */
	public function getTopNTags($num){
		$tags = Tag::get();
		if ($num < count($tags)){
			$top_n = array_slice($tags, 0, $num, true);
			return \Response::json(array("data" => array("tags" => $top_n)));
		}
		else{
			return \Response::json(array("data" => array("tags" => $tags)));
		}
	}

	/**
	 * Get a tag JSON by a given tag_id
	 * method: get
	 * route: api/tags/<tag_id>
	 * @param  int $tag_id
	 * @return json {"success": true or false, "data": {"tag": tag}};
	 */	
	public function show($tag_id)
	{
		$tag = Tag::find($tag_id);
		if ($tag == NULL){
			return \Response::json(array("success" => false));
		}
		return \Response::json(["success" => true, "data" => array("tag" => $tag)]);
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
