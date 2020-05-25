<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

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

    public function __construct(?Exporter $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * @inheritDoc
     */
    public function onStart(API\Span $span): void
    {
    }

    /**
     * @inheritDoc
     */
    public function onEnd(API\Span $span): void
    {
        if ($this->running) {
            // @todo only spans with SampleFlag === true should be exported according to
            // https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/sdk-tracing.md#sampling
            if (null !== $this->exporter) {
                $this->exporter->export([$span]);
            }
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
