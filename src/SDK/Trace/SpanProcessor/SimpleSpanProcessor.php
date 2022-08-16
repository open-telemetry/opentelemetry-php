<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
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
    public function onStart(ReadWriteSpanInterface $span, Context $parentContext): void
    {
    }

    /** @inheritDoc */
    public function onEnd(ReadableSpanInterface $span): void
    {
        if (!$this->running || !$span->getContext()->isSampled()) {
            return;
        }

        if (null !== $this->exporter) {
            $this->exporter->export([$span->toSpanData()])->await();
        }
    }

    /** @inheritDoc */
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        if (!$this->running || $this->exporter === null) {
            return true;
        }

        $this->exporter->forceFlush();

        return true;
    }

    /** @inheritDoc */
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if (!$this->running) {
            return true;
        }

        $this->running = false;
        self::logDebug('Shutting down span processor');

        if (null !== $this->exporter) {
            return $this->forceFlush() && $this->exporter->shutdown();
        }

        return true;
    }
}
