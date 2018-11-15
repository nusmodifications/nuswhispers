<?php

namespace NUSWhispers\Http\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use NUSWhispers\Models\Tag;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class GetTags
{
    /**
     * Retireve tags; sorted by popularity.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param array $args The arguments that were passed into the field.
     * @param GraphQLContext|null $context Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     *
     * @return mixed
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        return Tag::query()
            ->selectRaw('tags.*, (confessions.fb_like_count + (confessions.fb_comment_count * 2)) / POW(DATEDIFF(NOW(), confessions.status_updated_at) + 2, 1.8) AS `popularity_rating`')
            ->join('confession_tags', 'tags.confession_tag_id', '=', 'confession_tags.confession_tag_id')
            ->join('confessions', 'confessions.confession_id', '=', 'confession_tags.confession_id')
            ->where('confession_tag', 'REGEXP', '#[a-z]+')
            ->groupBy('confession_tags.confession_tag_id')
            ->orderBy('popularity_rating', 'desc')
            ->orderBy('confessions.status_updated_at', 'desc');
    }
}
