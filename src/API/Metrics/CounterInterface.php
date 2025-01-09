<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use OpenTelemetry\Context\ContextInterface;

interface CounterInterface extends SynchronousInstrument
{
    /**
     * @param float|int $amount non-negative amount to increment by
     * @param iterable<non-empty-string, string|bool|float|int|array|null> $attributes
     *        attributes of the data point
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#add
     */
    public function add(float|int $amount, iterable $attributes = [], ContextInterface|false|null $context = null): void;
}
