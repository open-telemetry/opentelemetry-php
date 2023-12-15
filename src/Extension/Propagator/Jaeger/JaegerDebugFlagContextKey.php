<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\Jaeger;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeyInterface;

/**
 * @psalm-internal \OpenTelemetry
 */
final class JaegerDebugFlagContextKey
{
    private const KEY_NAME = 'jaeger-debug-key';

    private static ?ContextKeyInterface $instance = null;

    public static function instance(): ContextKeyInterface
    {
        if (self::$instance === null) {
            self::$instance = Context::createKey(self::KEY_NAME);
        }

        return self::$instance;
    }
}
