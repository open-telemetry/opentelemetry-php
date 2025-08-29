<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use Opentelemetry\Proto\Common\V1\AnyValue;
use Opentelemetry\Proto\Common\V1\ArrayValue;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Common\V1\KeyValueList;

final class AttributesConverter
{
    /**
     * @param ((int|string|string[])[]|int)[]|string $value
     *
     * @psalm-param 'â'|array{0?: 1, 1?: 2, 2?: 3, 3?: 4, 4?: 5, nested?: list{0: 123, 1: 'abc'|456, 2?: array{sub: 'val'}}} $value
     */
    public static function convertAnyValue(array|string $value): AnyValue
    {
        $result = new AnyValue();
        if (is_array($value)) {
            if (self::isSimpleArray($value)) {
                $values = new ArrayValue();
                foreach ($value as $element) {
                    /** @psalm-suppress InvalidArgument */
                    $values->getValues()[] = self::convertAnyValue($element);
                }
                $result->setArrayValue($values);
            } else {
                $values = new KeyValueList();
                foreach ($value as $key => $element) {
                    /** @psalm-suppress InvalidArgument */
                    $values->getValues()[] = new KeyValue(['key' => $key, 'value' => self::convertAnyValue($element)]);
                }
                $result->setKvlistValue($values);
            }
        }
        if (is_int($value)) {
            $result->setIntValue($value);
        }
        if (is_bool($value)) {
            $result->setBoolValue($value);
        }
        if (is_float($value)) {
            $result->setDoubleValue($value);
        }
        if (is_string($value)) {
            if (self::isUtf8($value)) {
                $result->setStringValue($value);
            } else {
                $result->setBytesValue($value);
            }
        }

        return $result;
    }

    private static function isUtf8(string $value): bool
    {
        return \extension_loaded('mbstring')
            ? \mb_check_encoding($value, 'UTF-8')
            : (bool) \preg_match('//u', $value);
    }

    /**
     * Test whether an array is simple (non-KeyValue)
     */
    public static function isSimpleArray(array $value): bool
    {
        return $value === [] || array_key_first($value) === 0;
    }
}
