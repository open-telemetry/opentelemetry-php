<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\Propagation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;

/**
 * @psalm-internal \OpenTelemetry
 */
final class B3DebugFlagContextKey
{
    private const KEY_NAME = 'OpenTelemetry Context Key B3 Debug Flag';

    private static ?ContextKey $instance = null;

    public static function instance(): ContextKey
    {
        if (self::$instance === null) {
            self::$instance = Context::createKey(self::KEY_NAME);
        }

        return self::$instance;
    }
}
