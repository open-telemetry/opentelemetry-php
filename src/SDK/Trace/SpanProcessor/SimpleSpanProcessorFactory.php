<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

class SimpleSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    public function create(SpanProcessorContext $context): SpanProcessorInterface
    {
        return new SimpleSpanProcessor($context->exporter);
    }

    public function type(): string
    {
        return 'simple';
    }

    public function priority(): int
    {
        return 0;
    }
}
