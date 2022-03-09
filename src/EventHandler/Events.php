<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler;

interface Events
{
    public const START = 'StartSpanEvent';
    public const END = 'EndSpanEvent';
}
