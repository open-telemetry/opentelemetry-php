<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\Context\Context;

/** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk.md#span-processor */
interface SpanProcessor
{
    /**
     * This method is called when a span is started.
     * This method is called synchronously on the thread that started the span,
     * therefore it should not block or throw exceptions.
     */
    public function onStart(ReadWriteSpan $span, ?Context $parentContext = null): void;

    /**
     * This method  is called when a span is ended.
     * This method is called synchronously on the execution thread,
     * therefore it should not block or throw an exception.
     */
    public function onEnd(ReadableSpan $span): void;

    /**
     * Export all ended spans to the configured Exporter that have not yet been exported.
     */
    public function forceFlush(): void;

    /**
     * Cleanup; after shutdown, calling onStart, onEnd, or forceFlush is invalid
     */
    public function shutdown(): void;
}
