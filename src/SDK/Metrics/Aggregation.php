<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Data\Data;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Data\Temporality;

/**
 * @template T
 */
interface Aggregation
{

    /**
     * @return T
     */
    public function initialize();

    /**
     * @param T $summary
     * @param float|int $value
     */
    public function record($summary, $value, AttributesInterface $attributes, Context $context, int $timestamp): void;

    /**
     * @param T $left
     * @param T $right
     * @return T
     */
    public function merge($left, $right);

    /**
     * @param T $left
     * @param T $right
     * @return T
     */
    public function diff($left, $right);

    /**
     * @param array<AttributesInterface> $attributes
     * @param array<T> $summaries
     * @param array<list<Exemplar>> $exemplars
     * @param string|Temporality $temporality
     */
    public function toData(
        array $attributes,
        array $summaries,
        array $exemplars,
        int $startTimestamp,
        int $timestamp,
        $temporality
    ): Data;
}
