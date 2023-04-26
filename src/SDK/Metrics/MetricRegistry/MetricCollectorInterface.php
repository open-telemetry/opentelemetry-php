<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricRegistry;

/**
 * @internal
 */
interface MetricCollectorInterface
{
    public function collectAndPush(iterable $streamIds): void;
}
