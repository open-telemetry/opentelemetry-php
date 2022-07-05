<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoir;

final class ViewProjection {

    public function __construct(
        public readonly string $name,
        public readonly ?string $unit,
        public readonly ?string $description,
        public readonly ?AttributeProcessor $attributeProcessor,
        public readonly Aggregation $aggregation,
        public readonly ?ExemplarReservoir $exemplarReservoir,
    ) {}
}
