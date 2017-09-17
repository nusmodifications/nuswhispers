<?php

namespace NUSWhispers\Http\Controllers;

use Illuminate\Support\Facades\DB;
use NUSWhispers\Models\Tag;

class TagsController extends Controller
{
    /**
     * Get all the existing tags JSON in sorted order
     * method: get
     * route: api/tags.
     *
     * @return mixed
     */
    public function index()
    {
        $output = cache()->remember('tags', config('cache.api.timeout'), function () {
            return ['data' => ['tags' => $this->getSortedTags()]];
        });

        return response()->json($output);
    }

    /**
     * Get a tag JSON by a given tag_id
     * method: get
     * route: api/tags/<tag_id>.
     *
     * @param int $tag_id
     *
     * @return mixed
     */
    public function show($tag_id)
    {
        $tag = Tag::find($tag_id);
        if ($tag === null) {
            return response()->json(['success' => false]);
        }

        return response()->json(['success' => true, 'data' => ['tag' => $tag]]);
    }

    /**
     * Get the top n tags JSON sorted by number of associated posts
     * method: get
     * route: api/tags/top/<num> (if not clashes with api below).
     *
     * @param int $num
     *
     * @return mixed
     */
    public function topNTags($num = 5)
    {
        $num = ($num > 20) ? 5 : $num;

        $output = cache()->remember('top_' . $num . '_tags', config('cache.api.timeout'), function () use ($num) {
            $tags = DB::table('tags')
                ->join('confession_tags', 'tags.confession_tag_id', '=', 'confession_tags.confession_tag_id')
                ->join('confessions', 'confessions.confession_id', '=', 'confession_tags.confession_id')
                ->where('confession_tag', 'REGEXP', '#[a-z]+')
                ->select(\DB::raw('tags.*, (confessions.fb_like_count + (confessions.fb_comment_count * 2)) / POW(DATEDIFF(NOW(), confessions.status_updated_at) + 2, 1.8) AS `popularity_rating`'))
                ->groupBy('confession_tag_id')
                ->orderBy('popularity_rating', 'DESC')
                ->orderBy('status_updated_at', 'DESC')
                ->limit($num)
                ->get();

            return ['data' => ['tags' => $this->transformTags($tags)]];
        });

        return response()->json($output);
    }

    /**
     * Get all tags in sorted order according to number of posts a tag belongs to.
     *
     * @return mixed
     */
    protected function getSortedTags()
    {
        $tags = Tag::where('confession_tag', 'REGEXP', '#[a-z]+')->get()
            ->filter(function ($tag) {
                return $tag->confessions()->approved()->count() > 0;
            })
            ->sortBy(function ($tag) {
                return -$tag->confessions()->approved()->count();
            });

        return array_values($tags->toArray());
    }

    /**
     * Transform tags to its correct type.
     *
     * @param $tags
     *
     * @return mixed
     */
    protected function transformTags($tags)
    {
        return collect($tags)
            ->map(function ($tag) {
                return [
                    'confession_tag_id' => (string) $tag->confession_tag_id,
                    'confession_tag' => (string) $tag->confession_tag,
                    'popularity_rating' => $tag->popularity_rating,
                ];
            })
            ->all();
    }
}
