<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Instrumentation;

use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * Provides access to the globally configured instrumentation instances.
 */
final class Globals
{
    public static function tracerProvider(): TracerProviderInterface
    {
        static $noop;

        return Context::getCurrent()->get(ContextKeys::tracerProvider()) ?? $noop ??= new NoopTracerProvider();
    }

    public static function meterProvider(): MeterProviderInterface
    {
        static $noop;

        return Context::getCurrent()->get(ContextKeys::meterProvider()) ?? $noop ??= new NoopMeterProvider();
    }

    public static function propagator(): TextMapPropagatorInterface
    {
        static $noop;

        return Context::getCurrent()->get(ContextKeys::propagator()) ?? $noop ??= new NoopTextMapPropagator();
    }
}
