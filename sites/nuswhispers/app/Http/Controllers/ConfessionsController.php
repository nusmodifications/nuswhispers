<?php namespace App\Http\Controllers;

use App\Models\Tag as Tag;
use App\Models\Confession as Confession;
use App\Models\FbUser as FbUser;
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
		$query = Confession::with('categories')
			->with('favourites')
			->orderBy('created_at', 'DESC');

		// TODO: change to order by status_updated_at and filter by featured when approval is ready
		// $query = Confession::orderBy('status_updated_at', 'DESC');
		$query->featured();
		if (\Input::get('timestamp')) {
			$query->where('status_updated_at', '<=', \Input::get('timestamp'));
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

	public function recent()
	{
		$query = Confession::with('categories')->with('favourites')->orderBy('created_at', 'DESC');
		// TODO: change to order by status_updated_at and filter by approved when approval is ready
		// $query = Confession::orderBy('status_updated_at', 'DESC');
		// $query->approved();
		if (\Input::get('timestamp')) {
			$query->where('status_updated_at', '<=', \Input::get('timestamp'));
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

	public function popular()
	{
		$query = Confession::select(\DB::raw('confessions.*'))
			->join('favourites', 'confessions.confession_id' , '=', 'favourites.confession_id')
			->groupBy('confessions.confession_id')
			->orderByRaw('COUNT(favourites.fb_user_id) DESC')
			->with('favourites')
			->with('categories');
		// TODO: change to order by status_updated_at and filter by approved when approval is ready
		// $query = Confession::orderBy('status_updated_at', 'DESC');
		// $query->approved();

		if (\Input::get('timestamp')) {
			$query->where('status_updated_at', '<=', \Input::get('timestamp'));
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

	public function category($categoryId)
	{
		$query = Confession::select(\DB::raw('confessions.*'))
			->join('confession_categories', 'confessions.confession_id' , '=', 'confession_categories.confession_id')
			->where('confession_categories.confession_category_id', '=', $categoryId)
			->with('favourites')
			->with('categories');
		// TODO: change to order by status_updated_at and filter by approved when approval is ready
		// $query = Confession::orderBy('status_updated_at', 'DESC');
		// $query->approved();

		if (\Input::get('timestamp')) {
			$query->where('status_updated_at', '<=', \Input::get('timestamp'));
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

	public function tag($tagName)
	{
		$query = Confession::select(\DB::raw('confessions.*'))
			->join('confession_tags', 'confessions.confession_id' , '=', 'confession_tags.confession_id')
			->join('tags', 'confession_tags.confession_tag_id' , '=', 'tags.confession_tag_id')
			->where('tags.confession_tag', '=', "#$tagName")
			->with('favourites')
			->with('categories');
		// TODO: change to order by status_updated_at and filter by approved when approval is ready
		// $query = Confession::orderBy('status_updated_at', 'DESC');
		// $query->approved();

		if (\Input::get('timestamp')) {
			$query->where('status_updated_at', '<=', \Input::get('timestamp'));
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

	public function favourites()
	{
		$fbUserId = \Session::get('fb_user_id');
		if ($fbUserId) {
			$query = Confession::select(\DB::raw('confessions.*'))
			->join('favourites', 'confessions.confession_id' , '=', 'favourites.confession_id')
			->where('favourites.fb_user_id', '=', $fbUserId)
			->with('favourites')
			->with('categories');
			// TODO: change to order by status_updated_at and filter by approved when approval is ready
			// $query = Confession::orderBy('status_updated_at', 'DESC');
			// $query->approved();

			if (\Input::get('timestamp')) {
				$query->where('status_updated_at', '<=', \Input::get('timestamp'));
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
			return \Response::json(['success' => true, 'data' => ['confessions' => $confessions]]);
		}
		return \Response::json(['success' => false, 'errors' =>['User not logged in.']]);
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

	/**
	 * Search for confessions which contains the search string
	 * method: get
	 * route: api/confessions/search/<searchString>?timestamp=<time>&offset=<offset>&count=<count>
	 * @param  string $searchString - non-empty string (of length at least 5? - maybe at least 1)
	 * @return json {"data": {"confessions": [Confession1, confession2, ...]}}
	 *                           an array of confession json
	 */
	public function search($searchString)
	{
		// Naive search ...
		$query = Confession::orderBy('status_updated_at', 'DESC')
			->where('content', 'LIKE', '%'.$searchString.'%')
			->with('favourites')
			->with('categories');

		// TODO: change to filter by approved when approval is ready
		// $query->approved();

		if (\Input::get('timestamp')) {
			$query->where('status_updated_at', '<=', \Input::get('timestamp'));
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
}
