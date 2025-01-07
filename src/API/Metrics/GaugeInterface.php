<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use OpenTelemetry\Context\ContextInterface;

/**
 * A synchronous instrument which can be used to record non-additive values.
 *
 * @see https://opentelemetry.io/docs/specs/otel/metrics/api/#gauge
 *
 * @experimental
 */
interface GaugeInterface extends SynchronousInstrument
{
    /**
     * @param float|int $amount current absolute value
     * @param iterable<non-empty-string, string|bool|float|int|array|null> $attributes
     *        attributes of the data point
     * @param ContextInterface|false|null $context execution context
     *
     * @see https://opentelemetry.io/docs/specs/otel/metrics/api/#record-1
     */
    public function record(float|int $amount, iterable $attributes = [], ContextInterface|false|null $context = null): void;
}
