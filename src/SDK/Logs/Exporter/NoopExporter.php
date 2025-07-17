<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

class NoopExporter implements LogRecordExporterInterface
{
    #[\Override]
    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        return new CompletedFuture(true);
    }

    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }
}
