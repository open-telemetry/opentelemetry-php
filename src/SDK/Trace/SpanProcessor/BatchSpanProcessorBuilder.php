<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class BatchSpanProcessorBuilder
{
    private ?MeterProviderInterface $meterProvider = null;

    public function __construct(private readonly SpanExporterInterface $exporter)
    {
    }

    public function setMeterProvider(MeterProviderInterface $meterProvider): self
    {
        $this->meterProvider = $meterProvider;

        return $this;
    }

    public function build(): BatchSpanProcessor
    {
        return new BatchSpanProcessor(
            $this->exporter,
            Clock::getDefault(),
            BatchSpanProcessor::DEFAULT_MAX_QUEUE_SIZE,
            BatchSpanProcessor::DEFAULT_SCHEDULE_DELAY,
            BatchSpanProcessor::DEFAULT_EXPORT_TIMEOUT,
            BatchSpanProcessor::DEFAULT_MAX_EXPORT_BATCH_SIZE,
            true,
            $this->meterProvider
        );
    }
}
