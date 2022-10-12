<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @psalm-internal OpenTelemetry
 */
final class ContextKeys
{
    public static function span(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey('opentelemetry-trace-span-key');
    }

    public static function baggage(): ContextKeyInterface
    {
        static $instance;

        return $instance ??= Context::createKey('opentelemetry-trace-baggage-key');
    }
}
