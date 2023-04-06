<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class LoggerSharedState
{
    private ResourceInfo $resource;
    /** @var LogRecordProcessorInterface[] */
    private array $processors = [];
    private LogRecordLimits $limits;
    private ?bool $shutdownResult = null;

    public function __construct(
        ResourceInfo $resource,
        LogRecordLimits $limits,
        array $processors
    ) {
        $this->resource = $resource;
        $this->limits = $limits;
        $this->processors = $processors;
    }
    public function hasShutdown(): bool
    {
        return null !== $this->shutdownResult;
    }

    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    /**
     * @return LogRecordProcessorInterface[]
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }

    public function getLogRecordLimits(): LogRecordLimits
    {
        return $this->limits;
    }

    /**
     * Returns `true` if all processors shut down successfully, else `false`
     * Subsequent calls to `shutdown` are a no-op.
     */
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->shutdownResult !== null) {
            return $this->shutdownResult;
        }
        $this->shutdownResult = true;
        foreach ($this->processors as $processor) {
            if (!$processor->shutdown($cancellation)) {
                $this->shutdownResult = false;
            }
        }

        return $this->shutdownResult;
    }
}
