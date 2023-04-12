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
    private ArrayObject $storage;

    public function __construct(?ArrayObject $storage = null)
    {
        $this->storage = $storage ?? new ArrayObject();
    }

    /**
     * @inheritDoc
     */
    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        foreach ($batch as $record) {
            $this->storage[] = $record;
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
