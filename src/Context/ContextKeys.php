<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @psalm-internal OpenTelemetry
 */
final class ContextKeys
{
    public static function span(): ContextKey
    {
        static $instance;

        return $instance ??= Context::createKey('opentelemetry-trace-span-key');
    }

    public static function baggage(): ContextKey
    {
        static $instance;

        return $instance ??= Context::createKey('opentelemetry-trace-baggage-key');
    }
}
