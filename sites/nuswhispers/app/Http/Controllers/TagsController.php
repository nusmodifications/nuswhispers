<?php namespace App\Http\Controllers;

use App\Models\Tag as Tag;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class TagsController extends Controller {


    /**
     * Get all tags in sorted order according to number of posts a tag belongs to.
     * @return array [tag1, tag2, ...]
     */
    private function getSortedTags()
    {
        $tags = Tag::where('confession_tag', 'REGEXP', '#[a-z]+')->get()
            ->filter(function ($tag)
            {
                return $tag->confessions()->approved()->count() > 0;
            })
            ->sortBy(function ($tag)
            {
                return -$tag->confessions()->approved()->count();
            });

        return array_values($tags->toArray());
    }


    /**
     * Get all the existing tags JSON in sorted order
     * method: get
     * route: api/tags
     * @return json {"data": {tags": [tag1, tag2, ...]}}
     */
    public function index()
    {
        $tags = $this->getSortedTags();
        return \Response::json(array("data" => array("tags" => $tags)));
    }

    /**
     * Get the top n tags JSON sorted by number of associated posts
     * method: get
     * route: api/tags/top/<num> (if not clashes with api below)
     * @param int $num
     * @return json {"data": {"tags": [tag1, tag2, ...]}}
     */
    public function topNTags($num)
    {
        $num = ($num > 20) ? 5 : $num;

        $tags = \DB::table('tags')
            ->join('confession_tags', 'tags.confession_tag_id', '=', 'confession_tags.confession_tag_id')
            ->join('confessions', 'confessions.confession_id', '=', 'confession_tags.confession_id')
            ->where('confession_tag', 'REGEXP', '#[a-z]+')
            ->select(\DB::raw('tags.*, (confessions.fb_like_count + (confessions.fb_comment_count * 2)) / POW(DATEDIFF(NOW(), confessions.status_updated_at) + 2, 1.8) AS `popularity_rating`'))
            ->groupBy('confession_tag_id')
            ->orderBy('popularity_rating', 'DESC')
            ->orderBy('status_updated_at', 'DESC')
            ->limit(5)
            ->get();

        return \Response::json(['data' => ['tags' => $tags]]);
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
        if ($tag == NULL) {
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
