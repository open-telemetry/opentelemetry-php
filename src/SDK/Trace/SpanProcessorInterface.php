<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;

/** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk.md#span-processor */
interface SpanProcessorInterface
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#onstart
     */
    public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#onendspan
     */
    public function onEnd(ReadableSpanInterface $span): void;

    /**
     * Export all ended spans to the configured Exporter that have not yet been exported.
     * Returns `true` if the flush was successful, otherwise `false`.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#forceflush-1
     */
    public function forceFlush(?CancellationInterface $cancellation = null): bool;

    /**
     * Cleanup; after shutdown, calling onStart, onEnd, or forceFlush is invalid
     * Returns `false` is the processor is already shutdown, otherwise `true`.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#shutdown-1
     */
    public function shutdown(?CancellationInterface $cancellation = null): bool;
}
