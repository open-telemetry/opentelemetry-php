<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Exporter;

use ArrayObject;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

class InMemoryExporter implements LogRecordExporterInterface
{
    public function __construct(private readonly ArrayObject $storage = new ArrayObject())
    {
    }

    /**
     * @inheritDoc
     */
    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        foreach ($batch as $record) {
            $this->storage->append($record);
        }

        return new CompletedFuture(true);
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    public function getStorage(): ArrayObject
    {
        return $this->storage;
    }
}
