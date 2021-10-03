<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

/**
 * Class SpanMultiProcessor is a SpanProcessor that forwards all events to an
 * array of SpanProcessors.
 */
final class SpanMultiProcessor implements SpanProcessorInterface
{
    /** @var list<SpanProcessorInterface> */
    private $processors = [];

    /** @param list<SpanProcessorInterface> $spanProcessors */
    public function __construct(
        array $spanProcessors
    ) {
        foreach ($spanProcessors as $processor) {
            $this->addSpanProcessor($processor);
        }
    }

    public function addSpanProcessor(SpanProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * @return SpanProcessorInterface[]
     */
    public function getSpanProcessors(): array
    {
        return $this->processors;
    }

    public function onStart(ReadWriteSpanInterface $span, ?Context $parentContext = null): void
    {
        foreach ($this->processors as $processor) {
            $processor->onStart($span, $parentContext);
        }
    }

    public function onEnd(ReadableSpanInterface $span): void
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
