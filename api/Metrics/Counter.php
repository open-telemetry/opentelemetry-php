<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface Counter extends Metric
{
    public function add(int $value): Counter;

    public function increment(): Counter;

    public function getValue(): int;
}
