<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Parser;

use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;

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
            } else {
                if ($value === null) {
                    unset($attributes[$name]);
                }
            }
        }

        return $attributes;
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
