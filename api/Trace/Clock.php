<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Clock
{
    /**
     * A combination of the Monotonic and Realtime Clocks
     * Monotonic time value in the first slot, as it'll get accessed more frequently in duration calculations.
     * @return array
     */
    public function moment(): array;
    public function now(): int;
}
