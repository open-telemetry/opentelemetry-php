<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

class HttpMetricsSpanProcessorFactory implements SpanProcessorFactoryInterface
{
    public function create(SpanProcessorContext $context): SpanProcessorInterface
    {
        return new HttpMetricsSpanProcessor($context->meterProvider);
    }

    public function type(): string
    {
        return 'http_metrics';
    }

    public function priority(): int
    {
        return 0;
    }
}
