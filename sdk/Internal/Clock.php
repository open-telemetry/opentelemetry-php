<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Internal;

use function microtime;

class Clock
{
    public function __construct()
    {
    }

    public function millitime(): int
    {
        $millitime = microtime(true) * 1e+6;

        return (int) \round($millitime);
    }
}
