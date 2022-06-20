<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Span;

trait TraceTrait
{
    protected static function getCurrentSpan(): SpanInterface
    {
        return Span::fromContext(Context::getCurrent());
    }

    protected static function setSpanIntoNewContext(SpanInterface $span): Context
    {
        return Context::getCurrent()->withContextValue($span);
    }
}
