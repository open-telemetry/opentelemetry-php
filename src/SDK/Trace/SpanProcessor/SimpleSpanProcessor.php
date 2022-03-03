<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Subscriber\Dispatcher;
use OpenTelemetry\SDK\Subscriber\Event\EndSpanEvent;
use OpenTelemetry\SDK\Subscriber\Event\StartSpanEvent;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

class SimpleSpanProcessor implements SpanProcessorInterface
{
    use LogsMessagesTrait;

    private ?SpanExporterInterface $exporter;
    private bool $running = true;

    public function __construct(SpanExporterInterface $exporter = null)
    {
        $this->exporter = $exporter;
    }

    /** @inheritDoc */
    public function onStart(ReadWriteSpanInterface $span, ?Context $parentContext = null): void
    {
        $event = new StartSpanEvent([$span]);
        Dispatcher::getinstance()->dispatch($event);
    }

    /** @inheritDoc */
    public function onEnd(ReadableSpanInterface $span): void
    {
        if (!$this->running || !$span->getContext()->isSampled()) {
            return;
        }

        if (null !== $this->exporter) {
            $this->exporter->export([$span->toSpanData()]);
        }

        $event = new EndSpanEvent([$span]);
        Dispatcher::getinstance()->dispatch($event);
    }

    /** @inheritDoc */
    public function forceFlush(): bool
    {
        return true;
    }

    /** @inheritDoc */
    public function shutdown(): bool
    {
        if (!$this->running) {
            return true;
        }

        $this->running = false;
        $this->logDebug('Shutting down span processor');

        if (null !== $this->exporter) {
            return $this->forceFlush() && $this->exporter->shutdown();
        }

        return true;
    }
}
