<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class InMemorySpanExporterFactory implements SpanExporterFactoryInterface
{
    public function create(): SpanExporterInterface
    {
        return new InMemoryExporter();
    }

    public function type(): string
    {
        return 'memory';
    }

    public function priority(): int
    {
        return 0;
    }
}
