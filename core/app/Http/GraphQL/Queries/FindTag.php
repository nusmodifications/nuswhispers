<?php

namespace NUSWhispers\Http\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use NUSWhispers\Models\Tag;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FindTag
{
    /**
     * Finds a specific tag.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param array $args The arguments that were passed into the field.
     * @param GraphQLContext|null $context Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     *
     * @return \NUSWhispers\Models\Tag
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo): ?Tag
    {
        return Tag::findOrFail($args['id'] ?? null);
    }
}
