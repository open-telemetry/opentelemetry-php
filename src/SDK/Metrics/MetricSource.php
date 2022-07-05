<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Metric;

interface MetricSource {

    public function collectionTimestamp(): int;

    public function collect(?int $timestamp): Metric;
}
