<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\API\Behavior\Internal\ConfiguredLogger;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Log\LoggerInterface;

class BatchSpanProcessorBuilder
{
    private ?MeterProviderInterface $meterProvider = null;
    private ?LoggerInterface $logger = null;

    public function __construct(private readonly SpanExporterInterface $exporter)
    {
    }

    public function setMeterProvider(MeterProviderInterface $meterProvider): self
    {
        $this->meterProvider = $meterProvider;

        return $this;
    }

    /**
     * Set the logger for the processor's internal messages. Without one, they go to the global internal
     * logger, filtered by its global level.
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

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
            $this->meterProvider,
            $this->logger ?? new ConfiguredLogger(),
        );
    }
}
