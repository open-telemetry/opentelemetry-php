<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Internal;

class Clock
{
    public function __construct()
    {
    }

    public function now(): Timestamp
    {
        return Timestamp::now();
    }
}
