<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\Exporter;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Trace as API;

class SimpleSpanProcessor implements SpanProcessor
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
    public function onStart(API\Span $span, ?Context $parentContext = null): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onEnd(API\Span $span): void
    {
        if (!$this->running) {
            return;
        }

        if (!$span->isSampled()) {
            return;
        }

        if (null !== $this->exporter) {
            $this->exporter->export([$span]);
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
