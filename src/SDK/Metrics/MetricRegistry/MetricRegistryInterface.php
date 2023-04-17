<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\MetricRegistry;

use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorFactoryInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricCollectorInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricStreamInterface;

interface MetricRegistryInterface {

    public function registerSynchronousStream(int $instrumentId, MetricStreamInterface $stream, MetricCollectorInterface $collector): int;

    public function registerAsynchronousStream(int $instrumentId, MetricStreamInterface $stream, MetricAggregatorFactoryInterface $aggregatorFactory): int;

    public function unregisterStream(int $streamId): void;

    public function collectAndPush(array $streamIds): void;
}
