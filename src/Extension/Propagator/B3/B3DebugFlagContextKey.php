<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\B3;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeyInterface;

/**
 * @psalm-internal \OpenTelemetry
 */
final class B3DebugFlagContextKey
{
    private const KEY_NAME = 'OpenTelemetry Context Key B3 Debug Flag';

    private static ?ContextKeyInterface $instance = null;

    public static function instance(): ContextKeyInterface
    {
        if (self::$instance === null) {
            self::$instance = Context::createKey(self::KEY_NAME);
        }

        return self::$instance;
    }
}
