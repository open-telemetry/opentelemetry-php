<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Metrics\Data\Data;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Data\Temporality;

/**
 * @template T
 */
interface Aggregation {

    /**
     * @return T
     */
    public function initialize(): mixed;

    /**
     * @param T $summary
     */
    public function record(mixed $summary, float|int $value, Attributes $attributes, Context $context, int $timestamp): void;

    /**
     * @param T $left
     * @param T $right
     * @return T
     */
    public function merge(mixed $left, mixed $right): mixed;

    /**
     * @param T $left
     * @param T $right
     * @return T
     */
    public function diff(mixed $left, mixed $right): mixed;

    /**
     * @param array<Attributes> $attributes
     * @param array<T> $summaries
     * @param array<list<Exemplar>> $exemplars
     */
    public function toData(
        array $attributes,
        array $summaries,
        array $exemplars,
        ?int $startTimestamp,
        int $timestamp,
        Temporality $temporality,
    ): Data;
}
