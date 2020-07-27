<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface UpDownCounter extends Counter
{
    public function subtract(int $value) : UpDownCounter;

    public function decrement() : UpDownCounter;
}
