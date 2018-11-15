<?php

namespace NUSWhispers\Http\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use NUSWhispers\Models\Category;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FindCategory
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param array $args The arguments that were passed into the field.
     * @param GraphQLContext|null $context Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     *
     * @return \NUSWhispers\Models\Category
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo): ?Category
    {
        return Category::findOrFail($args['id'] ?? null);
    }
}
