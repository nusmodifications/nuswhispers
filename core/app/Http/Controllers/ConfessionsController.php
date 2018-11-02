<?php

namespace NUSWhispers\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use NUSWhispers\Models\ApiKey;
use NUSWhispers\Models\Confession;
use NUSWhispers\Rules\ReCaptcha;
use NUSWhispers\Services\ConfessionService;
use NUSWhispers\Services\FacebookBatchProcessor;

class ConfessionsController extends Controller
{
    /**
     * Refresh cache every 5 minutes.
     */
    public const CACHE_TIMEOUT = 5;

    /**
     * Maximum confessions allowed in API.
     * We can't go any higher or Facebook will not allow it.
     */
    public const MAX_CONFESSION_COUNT = 10;

    /**
     * Facebook batch processor.
     *
     * @var \NUSWhispers\Services\FacebookBatchProcessor
     */
    protected $batchProcessor;

    /**
     * Confessions service.
     *
     * @var \NUSWhispers\Services\ConfessionService
     */
    protected $service;

    /**
     * Creates a new ConfessionsController instance.
     *
     * @param \NUSWhispers\Services\FacebookBatchProcessor  $batchProcessor
     * @param \NUSWhispers\Services\ConfessionService       $service
     */
    public function __construct(FacebookBatchProcessor $batchProcessor,
        ConfessionService $service)
    {
        $this->batchProcessor = $batchProcessor;
        $this->service = $service;
    }

    /**
     * Display a listing of the resource. Get confessions under a specific category.
     *
     * @param  \Illuminate\Http\Request
     * @param  mixed $categoryId
     *
     * @return \Illuminate\Http\Response
     */
    public function category(Request $request, $categoryId)
    {
        $cacheId = $this->resolveCacheIdentifier($request);
        $output = Cache::remember($cacheId, self::CACHE_TIMEOUT, function () use ($categoryId) {
            $query = Confession::query()
                ->orderBy('status_updated_at', 'DESC')
                ->join('confession_categories', 'confessions.confession_id', '=', 'confession_categories.confession_id')
                ->where('confession_categories.confession_category_id', '=', $categoryId)
                ->with('favourites', 'categories')
                ->approved();

            return $this->processList($query);
        });

        return response()->json($output);
    }

    /**
     * Display a listing of the resource. Get favourite confessions.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return \Illuminate\Http\Response
     */
    public function favourites(Request $request)
    {
        $fbUserId = session()->get('fb_user_id');

        if (! $fbUserId) {
            return response()->json(['success' => false, 'errors' => ['User not logged in.']]);
        }

        $cacheId = $fbUserId . '/' . $this->resolveCacheIdentifier($request);
        $output = Cache::remember($cacheId, self::CACHE_TIMEOUT, function () use ($fbUserId) {
            $query = Confession::query()
                ->join('favourites', 'confessions.confession_id', '=', 'favourites.confession_id')
                ->where('favourites.fb_user_id', '=', $fbUserId)
                ->orderBy('status_updated_at', 'DESC')
                ->with('favourites', 'categories')
                ->approved();

            return $this->processList($query);
        });

        return response()->json($output);
    }

    /**
     * Display a listing of the resource. Get featured confessions.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cacheId = $this->resolveCacheIdentifier($request);
        $output = Cache::remember($cacheId, self::CACHE_TIMEOUT, function () {
            $query = Confession::query()
                ->with('categories', 'favourites')
                ->orderBy('status_updated_at', 'DESC')
                ->featured();

            return $this->processList($query);
        });

        return response()->json($output);
    }

    /**
     * Display a listing of the resource. Get popular confessions.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return \Illuminate\Http\Response
     */
    public function popular(Request $request)
    {
        $cacheId = $this->resolveCacheIdentifier($request);
        $output = Cache::remember($cacheId, self::CACHE_TIMEOUT, function () {
            // Adapted from http://thisinterestsme.com/creating-whats-hot-algorithm-php-mysql/.
            $query = Confession::query()
                ->popular()
                ->orderBy('status_updated_at', 'DESC')
                ->with('favourites', 'categories')
                ->approved();

            return $this->processList($query);
        });

        return response()->json($output);
    }

    /**
     * Display a listing of recent confessions.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return \Illuminate\Http\Response
     */
    public function recent(Request $request)
    {
        $cacheId = $this->resolveCacheIdentifier($request);
        $output = Cache::remember($cacheId, self::CACHE_TIMEOUT, function () {
            $query = Confession::query()
                ->with('favourites', 'categories')
                ->orderBy('status_updated_at', 'DESC')
                ->approved();

            return $this->processList($query);
        });

        return response()->json($output);
    }

    /**
     * Search for confessions which contains the search string
     * method: get
     * route: api/confessions/search/<searchString>?timestamp=<time>&offset=<offset>&count=<count>.
     *
     * @param  \Illuminate\Http\Request
     * @param  string $searchString - non-empty string (of length at least 5? - maybe at least 1)
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, $searchString)
    {
        $cacheId = $this->resolveCacheIdentifier($request);
        $output = Cache::remember($cacheId, self::CACHE_TIMEOUT, function () use ($searchString) {
            // Naive search ...
            $query = Confession::query()
                ->orderBy('status_updated_at', 'DESC')
                ->where('content', 'LIKE', '%' . $searchString . '%')
                ->with('favourites', 'categories')
                ->approved();

            return $this->processList($query);
        });

        return response()->json($output);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $output = Cache::remember('confessions/' . $id, self::CACHE_TIMEOUT, function () use ($id) {
            $confession = Confession::with('categories')->with('favourites')->find($id);
            if ($confession && $confession->approved()) {
                // increment number of views
                $confession->views++;
                $confession->save();

                $confession = $this->batchProcessor->processConfession($confession);

                return ['success' => true, 'data' => ['confession' => $confession]];
            }

            return ['success' => false];
        });

        return response()->json($output);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fingerprintKey = config('app.fingerprint_key');

        $request->validate([
            'content' => 'required',
            'image' => 'url',
            'categories' => 'array',
            'captcha' => [
                'required_without:api_key',
                new ReCaptcha(),
            ],
            'api_key' => [
                'required_without:captcha',
                Rule::exists('api_keys', 'key'),
            ],
            $fingerprintKey => 'nullable|string',
        ], [
            'api_key.exists' => 'Invalid API key. Please try again or use reCAPTCHA.',
        ]);

        // Touch last_used_on timestamp.
        if ($key = ApiKey::query()->where('key', request()->input('api_key'))->first()) {
            $key->update(['last_used_on' => now()]);
        }

        $confession = $this->service->create([
            'content' => $request->input('content'),
            'images' => $request->input('image'),
            'categories' => $request->input('categories'),
            'token' => $request->input($fingerprintKey),
        ]);

        return response()->json([
            $fingerprintKey => $confession->fingerprint,
            'success' => $confession->exists,
        ]);
    }

    /**
     * List confessions based on a specific tag.
     *
     * @param  \Illuminate\Http\Request
     * @param  string $tagName
     *
     * @return \Illuminate\Http\Response
     */
    public function tag(Request $request, $tagName)
    {
        $cacheId = $this->resolveCacheIdentifier($request);
        $output = Cache::remember($cacheId, self::CACHE_TIMEOUT, function () use ($tagName) {
            $query = Confession::query()
                ->select('confessions.*')
                ->leftJoin('confession_tags', 'confessions.confession_id', '=', 'confession_tags.confession_id')
                ->leftJoin('tags', 'confession_tags.confession_tag_id', '=', 'tags.confession_tag_id')
                ->where(function ($query) use ($tagName) {
                    $query->where('tags.confession_tag', '=', "#$tagName")
                        ->orWhere('confessions.confession_id', '=', $tagName);
                })
                ->orderBy('status_updated_at', 'DESC')
                ->with('favourites', 'categories')
                ->distinct()
                ->approved();

            return $this->processList($query);
        });

        return response()->json($output);
    }

    /**
     * Filters query based on inputs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $input
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filterQuery($query, $input = [])
    {
        if ($timestamp = array_get($input, 'timestamp')) {
            $timestamp = $this->normalizeTimestamp($timestamp);
            $query->whereRaw('UNIX_TIMESTAMP(status_updated_at) <= ?', [$timestamp]);
        }

        $count = (int) array_get($input, 'count');
        $count = ! $count ? self::MAX_CONFESSION_COUNT : min($count, self::MAX_CONFESSION_COUNT);

        $query->take($count);

        if ($offset = (int) array_get($input, 'offset')) {
            $query->skip($offset);
        }

        return $query;
    }

    /**
     * Normalize the timestamp; up to the highest minute.
     *
     * @param int $timestamp
     *
     * @return int
     */
    protected function normalizeTimestamp($timestamp): int
    {
        $seconds = self::CACHE_TIMEOUT * 60;

        return ceil($timestamp / $seconds) * $seconds;
    }

    /**
     * Retrieve and transform the confessions list.
     *
     * @param  mixed $query
     *
     * @return array
     */
    protected function processList($query): array
    {
        $query = $this->filterQuery($query, request()->all());

        $confessions = $query->get();
        $confessions = $this->batchProcessor->processConfessions($confessions);

        return ['data' => ['confessions' => $confessions]];
    }

    /**
     * Resolves the cache identifier.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function resolveCacheIdentifier(Request $request): string
    {
        $url = $request->fullUrl();

        if ($timestamp = $request->input('timestamp')) {
            $url = str_replace($timestamp, $this->normalizeTimestamp($timestamp), $url);
        }

        return 'confessions/' . md5($url);
    }
}
