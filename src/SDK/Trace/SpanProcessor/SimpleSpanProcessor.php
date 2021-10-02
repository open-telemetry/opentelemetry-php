<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Exporter;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

class SimpleSpanProcessor implements SpanProcessorInterface
{
    /**
     * @var Exporter|null
     */
    private $exporter;

    /**
     * @var bool
     */
    private $running = true;

    public function __construct(?Exporter $exporter = null)
    {
        $this->exporter = $exporter;
    }

    /**
     * @inheritDoc
     */
    public function onStart(ReadWriteSpanInterface $span, ?Context $parentContext = null): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onEnd(ReadableSpanInterface $span): void
    {
        if (!$this->running) {
            return;
        }

        if (!$span->getContext()->isSampled()) {
            return;
        }

        if (null !== $this->exporter) {
            $this->exporter->export([$span->toSpanData()]);
        }
    }

    /**
     * @inheritDoc
     */
    public function forceFlush(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function shutdown(): void
    {
        $this->running = false;

        if (null !== $this->exporter) {
            $this->exporter->shutdown();
        }
    }
}
