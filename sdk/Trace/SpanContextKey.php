<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Context\ContextKey;

/**
 * Class SpanContextKey
 * @package OpenTelemetry\Sdk\Trace
 * @internal
 */
class SpanContextKey extends ContextKey
{
    private const KEY_NAME = 'opentelemetry-trace-span-key';

    /**
     * @var ContextKey
     */
    private static $instance;
    
    public static function instance(): ContextKey
    {
        if (self::$instance === null) {
            self::$instance = new ContextKey(self::KEY_NAME);
        }
        
        return self::$instance;
    }
}
