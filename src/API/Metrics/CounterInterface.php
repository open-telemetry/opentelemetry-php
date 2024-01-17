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
     * @param ContextInterface|false|null $context execution context
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#add
     */
    public function add($amount, iterable $attributes = [], $context = null): void;
}
