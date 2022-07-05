<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use OpenTelemetry\Context\Context;

interface Histogram
{

    /**
     * @param float|int $amount non-negative amount to record
     * @param iterable<non-empty-string, string|bool|float|int|array|null> $attributes
     *        attributes of the data point
     * @param Context|false|null $context execution context
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#record
     */
    public function record(float|int $amount, iterable $attributes = [], Context|false|null $context = null): void;
}
