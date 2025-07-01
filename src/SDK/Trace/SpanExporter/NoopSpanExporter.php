<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class NoopSpanExporter implements SpanExporterInterface
{
    /**
     * @inheritDoc
     */
    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        return new CompletedFuture(true);
    }

    /**
     * @inheritDoc
     */
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }
}
