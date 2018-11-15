<?php

namespace NUSWhispers\Http\GraphQL\Scalars;

use Carbon\Carbon;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;

class DateTimeType extends ScalarType
{
    /**
     * @var string
     */
    public $name = 'DateTime';

    /**
     * @var string
     */
    public $description = 'The `DateTime` scalar type represents time data, represented as an ISO-8601 encoded UTC date string.';

    /**
     * Serializes an internal value to include in a response.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function serialize($value)
    {
        if (! $value instanceof Carbon) {
            throw new InvariantViolation('DateTime is not an instance of DateTimeImmutable: ' . Utils::printSafe($value));
        }

        return $value->format(Carbon::ATOM);
    }

    /**
     * Parses an externally provided value (query variable) to use as an input
     *
     * In the case of an invalid value this method must throw an Exception
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseValue($value): ?Carbon
    {
        return Carbon::createFromFormat(Carbon::ATOM, $value) ?: null;
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input
     *
     * In the case of an invalid node or value this method must throw an Exception
     *
     * @param \GraphQL\Language\AST\Node $valueNode
     * @param array|null $variables
     *
     * @return mixed
     * @throws \Exception
     */
    public function parseLiteral($valueNode, array $variables = null): ?string
    {
        if ($valueNode instanceof StringValueNode) {
            return $valueNode->value;
        }

        return null;
    }
}
