<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Trace as API;

interface SpanProcessor
{
    /**
     * This method is called when a span is started.
     * This method is called synchronously on the thread that started the span,
     * therefore it should not block or throw exceptions.
     */
    public function onStart(API\Span $span, ?Context $parentContext = null): void;

    /**
     * This method  is called when a span is ended.
     * This method is called synchronously on the execution thread,
     * therefore it should not block or throw an exception.
     */
    public function onEnd(API\Span $span): void;

    /**
     * Export all ended spans to the configured Exporter that have not yet been exported.
     */
    public function forceFlush(): void;

    /**
     * Cleanup; after shutdown, calling onStart, onEnd, or forceFlush is invalid
     */
    public function shutdown(): void;
}
