<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler;

interface Events
{
    public const SPAN_START = 'StartSpanEvent';
    public const SPAN_END = 'EndSpanEvent';
}
