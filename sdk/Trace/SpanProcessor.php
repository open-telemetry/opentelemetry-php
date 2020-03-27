<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

interface SpanProcessor
{
    /**
     * This method is called when a span is started.
     * This method is called synchronously on the thread that started the span,
     * therefore it should not block or throw exceptions.
     */
    public function onStart(API\Span $span): void;

    /**
     * This method  is called when a span is ended.
     * This method is called synchronously on the execution thread,
     * therefore it should not block or throw an exception.
     */
    public function onEnd(API\Span $span): void;

    /**
     * Cleanup; after shutdown, calling onStart or onEnd is invalid
     */
    public function shutdown(): void;
}
