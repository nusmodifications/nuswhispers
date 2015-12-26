<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Confession;
use App\Models\Tag;
use App\Repositories\ConfessionsRepository;

class ConfessionsController extends Controller
{
    const MAX_CONFESSION_COUNT = 10;

    const MAX_TAG_COUNT = 30;

    protected $confessionsRepo;

    public function __construct(ConfessionsRepository $confessionsRepo)
    {
        $this->confessionsRepo = $confessionsRepo;
    }

    public function category($categoryId)
    {
        $query = Confession::orderBy('status_updated_at', 'DESC')
            ->join('confession_categories', 'confessions.confession_id', '=', 'confession_categories.confession_id')
            ->where('confession_categories.confession_category_id', '=', $categoryId)
            ->approved()
            ->with('favourites')
            ->with('categories');

        if (\Input::get('timestamp')) {
            $query->whereRaw('UNIX_TIMESTAMP(status_updated_at) <= ?', [\Input::get('timestamp')]);
        }
        if (\Input::get('count') > 0) {
            $query->take(\Input::get('count'));
            $query->skip(\Input::get('offset'));
        }

        $confessions = $query->get();
        foreach ($confessions as $confession) {
            $confession->status_updated_at_timestamp = $confession->status_updated_at->timestamp;
            $confession->getFacebookInformation();
        }
        return \Response::json(['data' => ['confessions' => $confessions]]);
    }

    public function favourites()
    {
        $fbUserId = \Session::get('fb_user_id');
        if ($fbUserId) {
            $query = Confession::join('favourites', 'confessions.confession_id', '=', 'favourites.confession_id')
                ->where('favourites.fb_user_id', '=', $fbUserId)
                ->orderBy('status_updated_at', 'DESC')
                ->approved()
                ->with('favourites')
                ->with('categories');

            if (intval(\Input::get('count')) == 0) {
                $count = self::MAX_CONFESSION_COUNT;
            } else {
                $count = min(intval(\Input::get('count')), self::MAX_CONFESSION_COUNT);
            }

            if (\Input::get('timestamp')) {
                $query->whereRaw('UNIX_TIMESTAMP(status_updated_at) <= ?', [\Input::get('timestamp')]);
            }

            $query->take($count);

            if (intval(\Input::get('offset')) > 0) {
                $query->skip(intval(\Input::get('offset')));
            }

            $confessions = $query->get();
            foreach ($confessions as $confession) {
                $confession->status_updated_at_timestamp = $confession->status_updated_at->timestamp;
                $confession->getFacebookInformation();
            }
            return \Response::json(['success' => true, 'data' => ['confessions' => $confessions]]);
        }
        return \Response::json(['success' => false, 'errors' => ['User not logged in.']]);
    }

    /**
     * Display a listing of the resource. Get featured confessions.
     *
     * @return Response
     */
    public function index()
    {
        $query = Confession::with('categories')
            ->with('favourites')
            ->featured()
            ->orderBy('status_updated_at', 'DESC');

        if (intval(\Input::get('count')) == 0) {
            $count = self::MAX_CONFESSION_COUNT;
        } else {
            $count = min(intval(\Input::get('count')), self::MAX_CONFESSION_COUNT);
        }

        if (\Input::get('timestamp')) {
            $query->whereRaw('UNIX_TIMESTAMP(status_updated_at) <= ?', [\Input::get('timestamp')]);
        }

        $query->take($count);

        if (intval(\Input::get('offset')) > 0) {
            $query->skip(intval(\Input::get('offset')));
        }

        $confessions = $query->get();
        foreach ($confessions as $confession) {
            $confession->status_updated_at_timestamp = $confession->status_updated_at->timestamp;
            $confession->getFacebookInformation();
        }

        return \Response::json(['data' => ['confessions' => $confessions]]);
    }

    public function popular()
    {
        $query = Confession::select(\DB::raw('confessions.*,
            (confessions.fb_like_count + (confessions.fb_comment_count * 2)) / POW(DATEDIFF(NOW(), confessions.status_updated_at) + 2, 1.8) AS popularity_rating'))
            ->orderBy('popularity_rating', 'DESC')
            ->orderBy('status_updated_at', 'DESC')
            ->approved()
            ->with('favourites')
            ->with('categories');

        if (intval(\Input::get('count')) == 0) {
            $count = self::MAX_CONFESSION_COUNT;
        } else {
            $count = min(intval(\Input::get('count')), self::MAX_CONFESSION_COUNT);
        }

        if (\Input::get('timestamp')) {
            $query->whereRaw('UNIX_TIMESTAMP(status_updated_at) <= ?', [\Input::get('timestamp')]);
        }

        $query->take($count);

        if (intval(\Input::get('offset')) > 0) {
            $query->skip(intval(\Input::get('offset')));
        }

        $confessions = $query->get();
        foreach ($confessions as $confession) {
            $confession->status_updated_at_timestamp = $confession->status_updated_at->timestamp;
            $confession->getFacebookInformation();
        }
        return \Response::json(['data' => ['confessions' => $confessions]]);
    }

    public function recent()
    {
        $query = Confession::with('categories')
            ->with('favourites')
            ->approved()
            ->orderBy('status_updated_at', 'DESC');

        if (intval(\Input::get('count')) == 0) {
            $count = self::MAX_CONFESSION_COUNT;
        } else {
            $count = min(intval(\Input::get('count')), self::MAX_CONFESSION_COUNT);
        }

        if (\Input::get('timestamp')) {
            $query->whereRaw('UNIX_TIMESTAMP(status_updated_at) <= ?', [\Input::get('timestamp')]);
        }

        $query->take($count);

        if (intval(\Input::get('offset')) > 0) {
            $query->skip(intval(\Input::get('offset')));
        }

        $confessions = $query->get();
        foreach ($confessions as $confession) {
            $confession->status_updated_at_timestamp = $confession->status_updated_at->timestamp;
            $confession->getFacebookInformation();
        }

        return \Response::json(['data' => ['confessions' => $confessions]]);
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
            ->where('content', 'LIKE', '%' . $searchString . '%')
            ->approved()
            ->with('favourites')
            ->with('categories');

        if (intval(\Input::get('count')) == 0) {
            $count = self::MAX_CONFESSION_COUNT;
        } else {
            $count = min(intval(\Input::get('count')), self::MAX_CONFESSION_COUNT);
        }

        if (\Input::get('timestamp')) {
            $query->whereRaw('UNIX_TIMESTAMP(status_updated_at) <= ?', [\Input::get('timestamp')]);
        }

        $query->take($count);

        if (intval(\Input::get('offset')) > 0) {
            $query->skip(intval(\Input::get('offset')));
        }

        $confessions = $query->get();
        foreach ($confessions as $confession) {
            $confession->status_updated_at_timestamp = $confession->status_updated_at->timestamp;
            $confession->getFacebookInformation();
        }
        return \Response::json(['data' => ['confessions' => $confessions]]);
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
        if ($confession && $confession->isApproved()) {
            // increment number of views
            $confession->views++;
            $confession->save();

            $confession->status_updated_at_timestamp = $confession->status_updated_at->timestamp;
            $confession->getFacebookInformation();
            return \Response::json(['success' => true, 'data' => ['confession' => $confession]]);
        }
        return \Response::json(['success' => false]);
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
            return \Response::json(['success' => false, 'errors' => ['reCAPTCHA' => ['The reCAPTCHA was not entered correctly. Please try again.']]]);
        }

        if (is_array(\Input::get('categories'))) {
            $res = $this->confessionsRepo->create([
                'content' => \Input::get('content'),
                'images' => \Input::get('image')
            ], \Input::get('categories'));
        } else {
            $res = $this->confessionsRepo->create([
                'content' => \Input::get('content'),
                'images' => \Input::get('image')
            ]);
        }

        return \Response::json(['success' => $res]);
    }

    public function tag($tagName)
    {
        $query = Confession::select('confessions.*')
            ->leftJoin('confession_tags', 'confessions.confession_id', '=', 'confession_tags.confession_id')
            ->leftJoin('tags', 'confession_tags.confession_tag_id', '=', 'tags.confession_tag_id')
            ->where(function ($query) use ($tagName) {
                $query->where('tags.confession_tag', '=', "#$tagName")
                    ->orWhere('confessions.confession_id', '=', $tagName);
            })
            ->orderBy('status_updated_at', 'DESC')
            ->approved()
            ->with('favourites')
            ->with('categories');

        if (intval(\Input::get('count')) == 0) {
            $count = self::MAX_TAG_COUNT;
        } else {
            $count = min(intval(\Input::get('count')), self::MAX_TAG_COUNT);
        }

        if (\Input::get('timestamp')) {
            $query->whereRaw('UNIX_TIMESTAMP(status_updated_at) <= ?', [\Input::get('timestamp')]);
        }

        $query->take($count);

        if (intval(\Input::get('offset')) > 0) {
            $query->skip(intval(\Input::get('offset')));
        }

        $confessions = $query->distinct()->get();

        foreach ($confessions as $confession) {
            $confession->status_updated_at_timestamp = $confession->status_updated_at->timestamp;
            $confession->getFacebookInformation();
        }
        return \Response::json(['data' => ['confessions' => $confessions]]);
    }
}
