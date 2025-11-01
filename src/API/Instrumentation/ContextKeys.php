<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\API\Logs\EventLoggerProviderInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeyInterface;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
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
     * @return ContextKeyInterface<ResponsePropagatorInterface>
     */
    public static function responsePropagator(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey(ResponsePropagatorInterface::class);
    }

    /**
     * @return ContextKeyInterface<LoggerProviderInterface>
     */
    public static function loggerProvider(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey(LoggerProviderInterface::class);
    }

    /**
     * @deprecated
     * @return ContextKeyInterface<EventLoggerProviderInterface>
     */
    public static function eventLoggerProvider(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey(EventLoggerProviderInterface::class);
    }
}
