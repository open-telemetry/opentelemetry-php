<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use function array_key_first;
use function count;

final class TextMapPropagator
{
    public static function composite(TextMapPropagatorInterface ...$propagators): TextMapPropagatorInterface
    {
        switch (count($propagators)) {
            case 0:
                return NoopTextMapPropagator::getInstance();
            case 1:
                /** @psalm-suppress PossiblyNullArrayOffset */
                return $propagators[array_key_first($propagators)];
            default:
               return new MultiTextMapPropagator($propagators);
        }
    }

    private function __construct()
    {
    }
}
