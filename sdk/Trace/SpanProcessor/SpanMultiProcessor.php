<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Trace as API;

/**
 * Class SpanMultiProcessor is a SpanProcessor that forwards all events to an
 * array of SpanProcessors.
 */
final class SpanMultiProcessor implements SpanProcessor
{
    /**
     * @var SpanProcessor[]
     */
    private $processors = [];

    public function addSpanProcessor(SpanProcessor $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * @return SpanProcessor[]
     */
    public function getSpanProcessors(): array
    {
        return $this->processors;
    }

    public function onStart(API\Span $span, ?Context $parentContext = null): void
    {
        foreach ($this->processors as $processor) {
            $processor->onStart($span, $parentContext);
        }
    }

    public function onEnd(API\Span $span): void
    {
        foreach ($this->processors as $processor) {
            $processor->onEnd($span);
        }
    }

    public function shutdown(): void
    {
        foreach ($this->processors as $processor) {
            $processor->shutdown();
        }
    }

    public function forceFlush(): void
    {
        foreach ($this->processors as $processor) {
            $processor->forceFlush();
        }
    }
}
