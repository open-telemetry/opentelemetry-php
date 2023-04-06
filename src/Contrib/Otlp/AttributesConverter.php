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

final class AttributesConverter
{
    public static function convertAnyValue($value): AnyValue
    {
        $result = new AnyValue();
        if (is_array($value)) {
            $values = new ArrayValue();
            foreach ($value as $element) {
                /** @psalm-suppress InvalidArgument */
                $values->getValues()[] = self::convertAnyValue($element);
            }
            $result->setArrayValue($values);
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
            $result->setStringValue($value);
        }

        return $result;
    }
}
