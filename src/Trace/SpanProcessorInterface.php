<?php

namespace OpenTelemetry\Trace;

use OpenTelemetry\Trace\Span;

interface SpanProcessorInterface
{
    /**
     * This method is called when a span is started.
     * This method is called synchronously on the thread that started the span,
     * therefore it should not block or throw exceptions.
     */
    public function onStart(Span $span): void;

    /**
     * This method  is called when a span is ended.
     * This method is called synchronously on the execution thread,
     * therefore it should not block or throw an exception.
     */
    public function onEnd(Span $span): void;

    /* The spec mentions a shutdown() function. We don't see this as necessary;
    * if an Exporter needs to clean up, it can use a destructor.
    */
}