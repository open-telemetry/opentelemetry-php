<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

/**
 * Class SpanMultiProcessor is a SpanProcessor that forwards all events to an
 * array of SpanProcessors.
 */
final class MultiSpanProcessor implements SpanProcessorInterface
{
    /** @var list<SpanProcessorInterface> */
    private array $processors = [];

    public function __construct(SpanProcessorInterface ...$spanProcessors)
    {
        foreach ($spanProcessors as $processor) {
            $this->addSpanProcessor($processor);
        }
    }

    public function addSpanProcessor(SpanProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }

    /** @return list<SpanProcessorInterface> */
    public function getSpanProcessors(): array
    {
        return $this->processors;
    }

    /** @inheritDoc */
    #[\Override]
    public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void
    {
        foreach ($this->processors as $processor) {
            $processor->onStart($span, $parentContext);
        }
    }

    /** @inheritDoc */
    #[\Override]
    public function onEnd(ReadableSpanInterface $span): void
    {
        foreach ($this->processors as $processor) {
            $processor->onEnd($span);
        }
    }

    /** @inheritDoc */
    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        $result = true;

        foreach ($this->processors as $processor) {
            $result = $result && $processor->shutdown();
        }

        return $result;
    }

    /** @inheritDoc */
    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        $result = true;

        foreach ($this->processors as $processor) {
            $result = $result && $processor->forceFlush();
        }

        return $result;
    }
}
