<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;

/**
 * Contains convinience methods for interacting with the current active span
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/api.md#context-interaction
 */
class SpanUtils
{
    /**
     * Retrieves the current active span from the current context
     * @return SpanInterface
     */
    public static function getCurrentSpan(): SpanInterface
    {
        return AbstractSpan::fromContext(Context::getCurrent());
    }

    /**
     * Creates a new context to insert a span into, that then become the current context and the current active span
     * @param SpanInterface $span the span to set into the new context
     * @return Context
     */
    public static function setSpanIntoNewContext(SpanInterface $span): Context
    {
        return Context::getCurrent()->withContextValue($span);
    }
}
