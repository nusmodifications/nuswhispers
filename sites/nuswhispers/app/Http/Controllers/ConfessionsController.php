<?php namespace App\Http\Controllers;

use App\Models\Tag as Tag;
use App\Models\Confession as Confession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ConfessionsController extends Controller {

	/**
	 * Display a listing of the resource. Get featured confessions.
	 *
	 * @return Response
	 */
	public function index()
	{
		$query = Confession::with('categories')->with('favourites')->orderBy('created_at', 'DESC');
		// TODO: change to order by status_updated_at and filter by featured when approval is ready
		// $query = Confession::orderBy('status_updated_at', 'DESC');
		// $query->featured();
		if (\Input::get('timestamp')) {
			$query = $query->where('status_updated_at', '<=', \Input::get('timestamp'));
		}
		if (\Input::get('count') > 0) {
			$query->take(\Input::get('count'));
			$query->skip(\Input::get('offset'));
		}

		$confessions = $query->get();
		foreach ($confessions as $confession) {
			$confession->created_at_timestamp = $confession->created_at->timestamp;
			$confession->getFacebookInformation();
		}

		return \Response::json(['data' => ['confessions' => $confessions]]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$validationRules = [
			'content' => 'required',
			'image' => 'url',
			'categories' => 'array',
			'captcha' => 'required'
		];

		$validator = \Validator::make(\Input::all(), $validationRules);

		if ($validator->fails()) {
			return \Response::json(['success' => false, 'errors' => $validator->messages()]);
		}

		// Check reCAPTCHA
		$captchaResponseJSON = file_get_contents(sprintf(\Config::get('services.reCAPTCHA.verify'), \Config::get('services.reCAPTCHA.key'), \Input::get('captcha')));
		$captchaResponse = json_decode($captchaResponseJSON);

		if (!$captchaResponse->success) {
			return \Response::json(['success' => false, 'errors' => ['The reCAPTCHA was not entered correctly. Please try again.']]);
		}

		$newConfession = new Confession;

		$newConfession->content = \Input::get('content');
		$newConfession->images = \Input::get('image');
		$newConfession->save();

		// Get all tags in content
		preg_match_all('/(#\w+)/', $newConfession->content, $matches);
		$tagNames = array_shift($matches); // get full pattern matches from match result
		$tagNames = array_unique($tagNames); // get all unique tags from match result
		foreach ($tagNames as $tagName) {
			$confessionTag = Tag::firstOrCreate(['confession_tag' => $tagName]);
			$newConfession->tags()->attach($confessionTag->confession_tag_id);
		}

		$newConfession->categories()->attach(\Input::get('categories'));
		$newConfession->save();

		return \Response::json(['success' => true]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$confession = Confession::with('categories')->with('favourites')->find($id);
		if ($confession) {
			$confession->created_at_timestamp = $confession->created_at->timestamp;
			$confession->getFacebookInformation();
			return \Response::json(['success' => true, 'data' => ['confession' => $confession]]);
		}
		return \Response::json(['success' => false]);
	}

}
