<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\Propagation;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * B3 is a propagator that supports the specification for the header
 * "b3" used for trace context propagation across service boundaries.
 * (https://github.com/openzipkin/b3-propagation)
 */
final class B3Propagator
{
    public static function getB3SingleHeaderInstance(): TextMapPropagatorInterface
    {
        return B3SinglePropagator::getInstance();
    }
    public static function getB3MultiHeaderInstance(): TextMapPropagatorInterface
    {
        return B3MultiPropagator::getInstance();
    }
}
