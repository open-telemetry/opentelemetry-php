<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View;

use Closure;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoir;
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
    public ?AttributeProcessor $attributeProcessor;
    /**
     * @var Closure(string|InstrumentType):Aggregation
     * @readonly
     */
    public Closure $aggregation;
    /**
     * @var Closure(Aggregation, string|InstrumentType):?ExemplarReservoir
     * @readonly
     */
    public Closure $exemplarReservoir;
    /**
     * @param Closure(string|InstrumentType): Aggregation $aggregation
     * @param Closure(Aggregation, string|InstrumentType): ?ExemplarReservoir $exemplarReservoir
     */
    public function __construct(?string $name, ?string $description, ?AttributeProcessor $attributeProcessor, Closure $aggregation, Closure $exemplarReservoir)
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
