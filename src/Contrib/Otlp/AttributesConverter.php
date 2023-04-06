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

        switch (true) {
            case is_array($value):
                $values = new ArrayValue();
                foreach ($value as $element) {
                    /** @psalm-suppress InvalidArgument */
                    $values->getValues()[] = self::convertAnyValue($element);
                }
                $result->setArrayValue($values);

                break;
            case is_int($value):
                $result->setIntValue($value);

                break;
            case is_bool($value):
                $result->setBoolValue($value);

                break;
            case is_float($value):
                $result->setDoubleValue($value);

                break;
            case is_string($value):
                $result->setStringValue($value);

                break;
        }

        return $result;
    }
}
