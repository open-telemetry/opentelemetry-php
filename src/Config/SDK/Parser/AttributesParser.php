<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Parser;

use function array_is_list;
use const FILTER_FLAG_ALLOW_HEX;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;
use function filter_var;
use InvalidArgumentException;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use function sprintf;
use function str_ends_with;
use function substr;

/**
 * @internal
 */
class AttributesParser
{
    public static function parseAttributesList(?string $list): array
    {
        return MapParser::parse($list);
    }

    /**
     * @param array{
     *     array{
     *         name: string,
     *         value: mixed,
     *         type: ?string,
     *     }
     * } $input
     */
    public static function parseAttributes(array $input): array
    {
        $attributes = array_column($input, 'value', 'name');
        foreach ($attributes as $name => $value) {
            if (is_array($value)) {
                $attributes[$name] = array_values(array_filter($value, fn ($v) => $v !== null));
            } elseif ($value === null) {
                unset($attributes[$name]);
            }
        }

        return $attributes;
    }

    /**
     * Substitution leaves a variable node's value as a string, so `type` decides how to read it.
     * A null type keeps the value as written.
     *
     * @throws InvalidArgumentException when the value does not match the type
     */
    public static function applyType(mixed $value, ?string $type): mixed
    {
        if ($value === null || $type === null) {
            return $value;
        }
        if (str_ends_with($type, '_array')) {
            $itemType = substr($type, 0, -6);
            if (!is_array($value) || !array_is_list($value)) {
                throw new InvalidArgumentException(sprintf('value must be a list of type %s', $itemType));
            }
            $value = array_values(array_filter($value, static fn ($v) => $v !== null));
            if ($value === []) {
                throw new InvalidArgumentException('value must not be an empty list');
            }

            return array_map(static fn ($v) => self::applyScalarType($v, $itemType), $value);
        }

        return self::applyScalarType($value, $type);
    }

    private static function applyScalarType(mixed $value, string $type): string|bool|int|float
    {
        $resolved = match ($type) {
            'string' => is_string($value) ? $value : null,
            'bool' => is_bool($value) ? $value : self::filterString($value, FILTER_VALIDATE_BOOLEAN),
            'int' => is_int($value) ? $value : self::filterString($value, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_HEX),
            'double' => is_int($value) || is_float($value) ? (float) $value : self::filterString($value, FILTER_VALIDATE_FLOAT),
            default => null,
        };

        return $resolved ?? throw new InvalidArgumentException(sprintf('value must be of type %s', $type));
    }

    private static function filterString(mixed $value, int $filter, int $flags = 0): mixed
    {
        return is_string($value) ? filter_var($value, $filter, $flags | FILTER_NULL_ON_FAILURE) : null;
    }

    public static function applyIncludeExclude(array $attributes, ?array $included, ?array $excluded): array
    {
        if ($included !== null) {
            $attributes = array_filter($attributes, static function ($k) use ($included) {
                foreach ($included as $pattern) {
                    $regex = '/^' . strtr(preg_quote($pattern, '/'), [
                            '\?' => '.',
                            '\*' => '.*',
                        ]) . '$/';
                    if (preg_match($regex, $k)) {
                        return true;
                    }
                }

                return false;
            }, ARRAY_FILTER_USE_KEY);
        }
        if ($excluded) {
            $attributes = array_filter($attributes, static function ($k) use ($excluded) {
                foreach ($excluded as $pattern) {
                    $regex = '/^' . strtr(preg_quote($pattern, '/'), [
                            '\?' => '.',
                            '\*' => '.*',
                        ]) . '$/';
                    if (preg_match($regex, $k)) {
                        return false;
                    }
                }

                return true;
            }, ARRAY_FILTER_USE_KEY);
        }

        return $attributes;
    }
}
