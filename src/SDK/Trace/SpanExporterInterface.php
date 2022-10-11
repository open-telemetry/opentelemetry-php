<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#span-exporter
 */
interface SpanExporterInterface
{
    /**
     * @param iterable<SpanDataInterface> $batch Batch of spans to export
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#exportbatch
     *
     * @psalm-return FutureInterface<bool>
     */
    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#shutdown-2 */
    public function shutdown(?CancellationInterface $cancellation = null): bool;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#forceflush-2 */
    public function forceFlush(?CancellationInterface $cancellation = null): bool;
}
