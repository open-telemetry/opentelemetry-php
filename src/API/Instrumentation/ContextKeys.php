<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeyInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * @internal
 */
final class ContextKeys
{
    /**
     * @return ContextKeyInterface<TracerProviderInterface>
     */
    public static function tracerProvider(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey(TracerProviderInterface::class);
    }

    /**
     * @return ContextKeyInterface<MeterProviderInterface>
     */
    public static function meterProvider(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey(MeterProviderInterface::class);
    }

    /**
     * @return ContextKeyInterface<TextMapPropagatorInterface>
     */
    public static function propagator(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey(TextMapPropagatorInterface::class);
    }

    /**
     * @return ContextKeyInterface<LoggerProviderInterface>
     */
    public static function loggerProvider(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey(LoggerProviderInterface::class);
    }
}
