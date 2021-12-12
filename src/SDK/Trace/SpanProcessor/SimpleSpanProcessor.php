<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Behavior\LoggerAwareTrait;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use Psr\Log\LoggerAwareInterface;

class SimpleSpanProcessor implements SpanProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?SpanExporterInterface $exporter;
    private bool $running = true;

    public function __construct(SpanExporterInterface $exporter = null)
    {
        $this->exporter = $exporter;
    }

    /** @inheritDoc */
    public function onStart(ReadWriteSpanInterface $span, ?Context $parentContext = null): void
    {
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
