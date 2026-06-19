<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\Incubating\Metrics\OtelIncubatingMetrics;

class LoggerSharedState
{
    private ?bool $shutdownResult = null;
    private readonly ?CounterInterface $logCreatedCounter;

    public function __construct(
        private readonly ResourceInfo $resource,
        private readonly LogRecordLimits $limits,
        private readonly LogRecordProcessorInterface $processor,
        ?MeterProviderInterface $meterProvider = null,
    ) {
        if ($meterProvider !== null) {
            $this->logCreatedCounter = $meterProvider
                ->getMeter('io.opentelemetry.sdk')
                ->createCounter(
                    OtelIncubatingMetrics::OTEL_SDK_LOG_CREATED,
                    '{log_record}',
                    'The number of logs submitted to enabled SDK Loggers',
                );
        } else {
            $this->logCreatedCounter = null;
        }
    }

    public function getLogCreatedCounter(): ?CounterInterface
    {
        return $this->logCreatedCounter;
    }

    public function hasShutdown(): bool
    {
        return null !== $this->shutdownResult;
    }

    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    public function getProcessor(): LogRecordProcessorInterface
    {
        return $this->processor;
    }

    public function getLogRecordLimits(): LogRecordLimits
    {
        return $this->limits;
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->shutdownResult !== null) {
            return $this->shutdownResult;
        }
        $this->shutdownResult = $this->processor->shutdown($cancellation);

        return $this->shutdownResult;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->processor->forceFlush($cancellation);
    }
}
