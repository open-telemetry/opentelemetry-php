<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\Noop;

use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;

final class NoopTracer implements TracerInterface
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function spanBuilder(string $spanName): SpanBuilderInterface
    {
        return new NoopSpanBuilder(Context::storage());
    }
}
