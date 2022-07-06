<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoir;
use function serialize;

final class MetricAggregator implements WritableMetricStream
{
    private ?AttributeProcessor $attributeProcessor;
    private Aggregation $aggregation;
    private ?ExemplarReservoir $exemplarReservoir;

    /** @var array<AttributesInterface> */
    private array $attributes = [];
    private array $summaries = [];

    public int $revision = 0;

    public function __construct(
        ?AttributeProcessor $attributeProcessor,
        Aggregation $aggregation,
        ?ExemplarReservoir $exemplarReservoir
    ) {
        $this->attributeProcessor = $attributeProcessor;
        $this->aggregation = $aggregation;
        $this->exemplarReservoir = $exemplarReservoir;
    }

    /**
     * @param float|int $value
     */
    public function record($value, AttributesInterface $attributes, Context $context, int $timestamp): void
    {
        $filteredAttributes = $this->attributeProcessor !== null
            ? $this->attributeProcessor->process($attributes, $context)
            : $attributes;
        $raw = $filteredAttributes->toArray();
        $index = $raw !== [] ? serialize($raw) : 0;
        $this->attributes[$index] = $filteredAttributes;
        $this->aggregation->record(
            $this->summaries[$index] ??= $this->aggregation->initialize(),
            $value,
            $attributes,
            $context,
            $timestamp,
        );

        if ($this->exemplarReservoir !== null) {
            $this->exemplarReservoir->offer($index, $value, $attributes, $context, $timestamp, $this->revision);
        }
    }

    public function collect(int $timestamp): Metric
    {
        $metric = new Metric($this->attributes, $this->summaries, $timestamp, $this->revision);

        $this->attributes = [];
        $this->summaries = [];
        $this->revision++;

        return $metric;
    }

    /**
     * @return array<list<Exemplar>>
     */
    public function exemplars(Metric $metric): array
    {
        return $this->exemplarReservoir && $metric->revision !== -1
            ? $this->exemplarReservoir->collect($metric->attributes, $metric->revision, $this->revision)
            : [];
    }
}
