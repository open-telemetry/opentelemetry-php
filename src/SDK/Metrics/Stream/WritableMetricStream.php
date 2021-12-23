<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context;
use OpenTelemetry\SDK\Attributes;

interface WritableMetricStream {

    public function record(float|int $value, Attributes $attributes, Context $context, int $timestamp): void;
}
