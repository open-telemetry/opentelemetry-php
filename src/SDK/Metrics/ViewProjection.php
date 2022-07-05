<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoir;

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
    public ?AttributeProcessor $attributeProcessor;
    /**
     * @readonly
     */
    public Aggregation $aggregation;
    /**
     * @readonly
     */
    public ?ExemplarReservoir $exemplarReservoir;
    public function __construct(string $name, ?string $unit, ?string $description, ?AttributeProcessor $attributeProcessor, Aggregation $aggregation, ?ExemplarReservoir $exemplarReservoir)
    {
        $this->name = $name;
        $this->unit = $unit;
        $this->description = $description;
        $this->attributeProcessor = $attributeProcessor;
        $this->aggregation = $aggregation;
        $this->exemplarReservoir = $exemplarReservoir;
    }
}
