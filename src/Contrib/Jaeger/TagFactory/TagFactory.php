<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger\TagFactory;

use Jaeger\Thrift\Tag;
use Jaeger\Thrift\TagType;

class TagFactory
{
    public static function create(string $key, $value): Tag
    {
        return self::createJaegerTagInstance(
            $key,
            self::convertValueToTypeJaegerTagsSupport($value)
        );
    }

    private static function convertValueToTypeJaegerTagsSupport($value)
    {
        if (is_array($value)) {
            return self::serializeArrayToString($value);
        }

        return $value;
    }

    private static function createJaegerTagInstance(string $key, $value)
    {
        if (is_bool($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::BOOL,
                'vBool' => $value,
            ]);
        }

        if (is_integer($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::LONG,
                'vLong' => $value,
            ]);
        }

        if (is_numeric($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::DOUBLE,
                'vDouble' => $value,
            ]);
        }

        return new Tag([
            'key' => $key,
            'vType' => TagType::STRING,
            'vStr' => (string) $value,
        ]);
    }

    private static function serializeArrayToString(array $arrayToSerialize): string
    {
        return self::recursivelySerializeArray($arrayToSerialize);
    }

    private static function recursivelySerializeArray($value): string
    {
        if (is_array($value)) {
            return join(',', array_map(function ($val) {
                return self::recursivelySerializeArray($val);
            }, $value));
        }

        // Casting false to string makes an empty string
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // Floats will lose precision if their string representation
        // is >=14 or >=17 digits, depending on PHP settings.
        // Can also throw E_RECOVERABLE_ERROR if $value is an object
        // without a __toString() method.
        // This is possible because OpenTelemetry\Trace\Span does not verify
        // setAttribute() $value input.
        return (string) $value;
    }
}
