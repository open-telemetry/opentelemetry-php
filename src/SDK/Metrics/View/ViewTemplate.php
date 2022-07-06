<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View;

use Closure;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\ViewProjection;

final class ViewTemplate
{

    /**
     * @readonly
     */
    public ?string $name;
    /**
     * @readonly
     */
    public ?string $description;
    /**
     * @readonly
     */
    public ?AttributeProcessorInterface $attributeProcessor;
    /**
     * @var Closure(string|InstrumentType):AggregationInterface
     * @readonly
     */
    public Closure $aggregation;
    /**
     * @var Closure(AggregationInterface, string|InstrumentType): (ExemplarReservoirInterface|null)
     * @readonly
     */
    public Closure $exemplarReservoir;
    /**
     * @param Closure(string|InstrumentType): AggregationInterface $aggregation
     * @param Closure(AggregationInterface, string|InstrumentType): (ExemplarReservoirInterface|null) $exemplarReservoir
     */
    public function __construct(?string $name, ?string $description, ?AttributeProcessorInterface $attributeProcessor, Closure $aggregation, Closure $exemplarReservoir)
    {
        $this->name = $name;
        $this->description = $description;
        $this->attributeProcessor = $attributeProcessor;
        $this->aggregation = $aggregation;
        $this->exemplarReservoir = $exemplarReservoir;
    }

    public function project(Instrument $instrument): ViewProjection
    {
        $aggregation = ($this->aggregation)($instrument->type);
        $exemplarReservoir = ($this->exemplarReservoir)($aggregation, $instrument->type);

        return new ViewProjection(
            $this->name ?? $instrument->name,
            $instrument->unit,
            $this->description ?? $instrument->description,
            $this->attributeProcessor,
            $aggregation,
            $exemplarReservoir,
        );
    }
}
