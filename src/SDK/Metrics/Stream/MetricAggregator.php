<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;
use function serialize;

/**
 * @internal
 */
final class MetricAggregator implements MetricAggregatorInterface
{
    /** @var array<AttributesInterface> */
    private array $attributes = [];
    private array $summaries = [];

    public function __construct(
        private readonly ?AttributeProcessorInterface $attributeProcessor,
        private readonly AggregationInterface $aggregation,
        private readonly ?ExemplarReservoirInterface $exemplarReservoir = null,
    ) {
    }

    public function record(float|int $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): void
    {
        $filteredAttributes = $this->attributeProcessor !== null
            ? $this->attributeProcessor->process($attributes, $context)
            : $attributes;
        $raw = $filteredAttributes->toArray();
        $index = $raw !== [] ? serialize($raw) : 0;
        $this->attributes[$index] ??= $filteredAttributes;
        $this->aggregation->record(
            $this->summaries[$index] ??= $this->aggregation->initialize(),
            $value,
            $attributes,
            $context,
            $timestamp,
        );

        if ($this->exemplarReservoir !== null) {
            $this->exemplarReservoir->offer($index, $value, $attributes, $context, $timestamp);
        }
    }

    public function collect(int $timestamp): Metric
    {
        $exemplars = $this->exemplarReservoir
            ? $this->exemplarReservoir->collect($this->attributes)
            : [];
        $metric = new Metric($this->attributes, $this->summaries, $timestamp, $exemplars);

        $this->attributes = [];
        $this->summaries = [];

        return $metric;
    }
}
