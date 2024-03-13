<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class LoggerSharedState
{
    private ?bool $shutdownResult = null;

    public function __construct(
        private readonly ResourceInfo $resource,
        private readonly LogRecordLimits $limits,
        private readonly LogRecordProcessorInterface $processor,
    ) {
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
