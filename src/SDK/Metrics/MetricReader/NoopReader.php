<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricReader;

use OpenTelemetry\SDK\Metrics\MetricReaderInterface;

class NoopReader implements MetricReaderInterface
{

    public function collect(): bool
    {
        return true;
    }

    public function shutdown(): bool
    {
        return true;
    }

    public function forceFlush(): bool
    {
        return true;
    }
}
