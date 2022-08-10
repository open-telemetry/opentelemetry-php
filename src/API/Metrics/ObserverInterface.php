<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface ObserverInterface
{

    /**
     * Records the given absolute datapoint.
     *
     * @param float|int $amount observed amount
     * @param iterable<non-empty-string, string|bool|float|int|array|null> $attributes
     *        attributes of the data point
     */
    public function observe($amount, iterable $attributes = []): void;
}
