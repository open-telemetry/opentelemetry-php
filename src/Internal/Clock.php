<?php

namespace OpenTelemetry\Internal;

class Clock
{
    /**
     * Return current timestamp in milliseconds
     */
    public function millitime(): int
    {
        return (int)(microtime(true) * 1000);
    }
}