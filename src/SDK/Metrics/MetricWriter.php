<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context;

interface MetricWriter {

    public function record(float|int $value, iterable $attributes = [], Context|false|null $context = null): void;
}
