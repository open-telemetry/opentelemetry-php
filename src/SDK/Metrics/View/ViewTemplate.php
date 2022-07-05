<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\View;

use Closure;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoir;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\ViewProjection;

final class ViewTemplate {

    /**
     * @param Closure(InstrumentType): Aggregation $aggregation
     * @param Closure(Aggregation, InstrumentType): ?ExemplarReservoir $exemplarReservoir
     */
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $description,
        public readonly ?AttributeProcessor $attributeProcessor,
        public readonly Closure $aggregation,
        public readonly Closure $exemplarReservoir,
    ) {}

    public function project(Instrument $instrument): ViewProjection {
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
