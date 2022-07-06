<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;

final class ViewProjection
{
    /**
     * @readonly
     */
    public string $name;
    /**
     * @readonly
     */
    public ?string $unit;
    /**
     * @readonly
     */
    public ?string $description;
    /**
     * @readonly
     */
    public ?AttributeProcessorInterface $attributeProcessor;
    /**
     * @readonly
     */
    public AggregationInterface $aggregation;
    /**
     * @readonly
     */
    public ?ExemplarReservoirInterface $exemplarReservoir;
    public function __construct(string $name, ?string $unit, ?string $description, ?AttributeProcessorInterface $attributeProcessor, AggregationInterface $aggregation, ?ExemplarReservoirInterface $exemplarReservoir)
    {
        $this->name = $name;
        $this->unit = $unit;
        $this->description = $description;
        $this->attributeProcessor = $attributeProcessor;
        $this->aggregation = $aggregation;
        $this->exemplarReservoir = $exemplarReservoir;
    }
}
