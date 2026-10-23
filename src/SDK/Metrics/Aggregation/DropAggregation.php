<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\Data;

/**
 * Drops all measurements. A view with this aggregation registers no stream, so an instrument
 * with no other view is disabled.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/metrics/sdk.md#drop-aggregation
 *
 * @implements AggregationInterface<null>
 */
final class DropAggregation implements AggregationInterface
{
    #[\Override]
    public function initialize(): null
    {
        return null;
    }

    #[\Override]
    public function record($summary, $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): void
    {
    }

    #[\Override]
    public function merge($left, $right): null
    {
        return null;
    }

    #[\Override]
    public function diff($left, $right): null
    {
        return null;
    }

    #[\Override]
    public function toData(
        array $attributes,
        array $summaries,
        array $exemplars,
        int $startTimestamp,
        int $timestamp,
        $temporality,
    ): Data\Gauge {
        return new Data\Gauge([]);
    }
}
