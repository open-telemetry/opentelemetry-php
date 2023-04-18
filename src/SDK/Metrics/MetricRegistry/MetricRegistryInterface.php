<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricRegistry;

use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorFactoryInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricStreamInterface;

/**
 * @internal
 */
interface MetricRegistryInterface extends MetricCollectorInterface
{
    public function registerSynchronousStream(Instrument $instrument, MetricStreamInterface $stream, MetricAggregatorInterface $aggregator): int;

    public function registerAsynchronousStream(Instrument $instrument, MetricStreamInterface $stream, MetricAggregatorFactoryInterface $aggregatorFactory): int;

    public function unregisterStream(int $streamId): void;
}
