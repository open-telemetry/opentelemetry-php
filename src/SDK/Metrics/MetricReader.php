<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface MetricReader
{
    public function collect(): bool;

    public function shutdown(): bool;

    public function forceFlush(): bool;
}
