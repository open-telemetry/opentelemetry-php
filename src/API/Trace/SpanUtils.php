<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;

class SpanUtils
{
    public static function getCurrentSpan(): SpanInterface
    {
        return AbstractSpan::fromContext(Context::getCurrent());
    }

    public static function setSpanIntoNewContext(SpanInterface $span): Context
    {
        return Context::getCurrent()->withContextValue($span);
    }
}
