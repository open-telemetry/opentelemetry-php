<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\ReadableSpan;
use OpenTelemetry\Sdk\Trace\ReadWriteSpan;
use OpenTelemetry\Sdk\Trace\SpanProcessor;

/**
 * Class SpanMultiProcessor is a SpanProcessor that forwards all events to an
 * array of SpanProcessors.
 */
final class SpanMultiProcessor implements SpanProcessor
{
    /** @var list<SpanProcessor> */
    private $processors = [];

    /** @param list<SpanProcessor> $spanProcessors */
    public function __construct(
        array $spanProcessors
    ) {
        foreach ($spanProcessors as $processor) {
            $this->addSpanProcessor($processor);
        }
    }

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

    public function onStart(ReadWriteSpan $span, ?Context $parentContext = null): void
    {
        foreach ($this->processors as $processor) {
            $processor->onStart($span, $parentContext);
        }
    }

    public function onEnd(ReadableSpan $span): void
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
