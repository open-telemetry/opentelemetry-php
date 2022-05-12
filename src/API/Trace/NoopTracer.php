<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\ContextStorage;

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
        return new NoopSpanBuilder(ContextStorage::default());
    }
}
