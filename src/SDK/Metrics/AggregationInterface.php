<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Data\Temporality;

/**
 * @psalm-template T
 */
interface AggregationInterface
{
    /**
     * @psalm-return T
     */
    public function initialize();

    /**
     * @psalm-param T $summary
     * @psalm-param float|int $value
     */
    public function record($summary, $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): void;

    /**
     * @psalm-param T $left
     * @psalm-param T $right
     * @psalm-return T
     */
    public function merge($left, $right);

    /**
     * @psalm-param T $left
     * @psalm-param T $right
     * @psalm-return T
     */
    public function diff($left, $right);

    /**
     * @param array<AttributesInterface> $attributes
     * @psalm-param array<T> $summaries
     * @param array<list<Exemplar>> $exemplars
     * @param string|Temporality $temporality
     */
    public function toData(
        array $attributes,
        array $summaries,
        array $exemplars,
        int $startTimestamp,
        int $timestamp,
        $temporality,
    ): DataInterface;
}
