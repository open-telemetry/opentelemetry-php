<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class LoggerSharedState
{
    private ResourceInfo $resource;
    private LogRecordProcessorInterface $processor;
    private LogRecordLimits $limits;
    private ?bool $shutdownResult = null;

    public function __construct(
        ResourceInfo $resource,
        LogRecordLimits $limits,
        LogRecordProcessorInterface $processor
    ) {
        $this->resource = $resource;
        $this->limits = $limits;
        $this->processor = $processor;
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
