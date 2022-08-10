<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use Closure;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use SplQueue;

class SimpleSpanProcessor implements SpanProcessorInterface
{
    private SpanExporterInterface $exporter;

    private bool $running = false;
    /** @var SplQueue<Closure> */
    private SplQueue $queue;

    private bool $closed = false;

    public function __construct(SpanExporterInterface $exporter)
    {
        $this->exporter = $exporter;

        $this->queue = new SplQueue();
    }

    public function onStart(ReadWriteSpanInterface $span, Context $parentContext): void
    {
    }

    public function onEnd(ReadableSpanInterface $span): void
    {
        if ($this->closed) {
            return;
        }
        if (!$span->getContext()->isSampled()) {
            return;
        }

        $spanData = $span->toSpanData();
        $this->flush(fn () => $this->exporter->export([$spanData])->await());
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return $this->queue->isEmpty();
        }

        return $this->flush(fn (): bool => $this->exporter->forceFlush($cancellation));
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return $this->queue->isEmpty();
        }

        $this->closed = true;

        return $this->flush(fn (): bool => $this->exporter->shutdown($cancellation));
    }

    private function flush(Closure $task): bool
    {
        $this->queue->enqueue($task);

        if ($this->running) {
            return false;
        }

        $this->running = true;

        try {
            while (!$this->queue->isEmpty()) {
                $this->queue->dequeue()();
            }
        } finally {
            $this->running = false;
        }

        return true;
    }
}
