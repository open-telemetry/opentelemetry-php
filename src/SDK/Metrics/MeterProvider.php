<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoir;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

interface MeterProvider extends \OpenTelemetry\API\Metrics\MeterProvider
{

    /**
     * @param SelectionCriteria $criteria
     * @param string|null $name
     * @param string|null $description
     * @param array|null $attributeKeys
     * @param Closure(string|InstrumentType): Aggregation|null $aggregation
     * @param Closure(Aggregation, string|InstrumentType): ?ExemplarReservoir|null $exemplarReservoir
     * @return void
     */
    public function registerView(
        SelectionCriteria $criteria,
        ?string $name = null,
        ?string $description = null,
        ?array $attributeKeys = null,
        ?Closure $aggregation = null,
        ?Closure $exemplarReservoir = null
    ): void;

    public function shutdown(): bool;

    public function forceFlush(): bool;
}
