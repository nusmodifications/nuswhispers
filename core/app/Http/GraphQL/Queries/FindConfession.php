<?php

namespace NUSWhispers\Http\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use NUSWhispers\Models\Confession;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FindConfession
{
    /**
     * Finds a specific confession.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param array $args The arguments that were passed into the field.
     * @param GraphQLContext|null $context Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     *
     * @return \NUSWhispers\Models\Confession
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo): ?Confession
    {
        /** @var \NUSWhispers\Models\Confession $confession */
        $confession = Confession::query()->approved()->findOrFail($args['id'] ?? null);

        // Increment views.
        $confession->views++;
        $confession->save();

        return $confession;
    }
}
