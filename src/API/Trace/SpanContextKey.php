<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;

/**
 * @psalm-internal \OpenTelemetry
 */
final class SpanContextKey extends ContextKey
{
    private const KEY_NAME = 'opentelemetry-trace-span-key';

    /** @var ContextKey */
    private static $instance;

    public static function instance(): ContextKey
    {
        if (self::$instance === null) {
            self::$instance = Context::createKey(self::KEY_NAME);
        }

        return self::$instance;
    }
}
