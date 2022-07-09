<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteriaInterface;

interface MeterProviderInterface extends \OpenTelemetry\API\Metrics\MeterProviderInterface
{

    /**
     * @param SelectionCriteriaInterface $criteria
     * @param string|null $name
     * @param string|null $description
     * @param array|null $attributeKeys
     * @param Closure(string|InstrumentType): AggregationInterface|null $aggregation
     * @param Closure(AggregationInterface, string|InstrumentType): (ExemplarReservoirInterface|null)|null $exemplarReservoir
     * @return void
     */
    public function registerView(
        SelectionCriteriaInterface $criteria,
        ?string $name = null,
        ?string $description = null,
        ?array $attributeKeys = null,
        ?Closure $aggregation = null,
        ?Closure $exemplarReservoir = null
    ): void;

    public function shutdown(): bool;

    public function forceFlush(): bool;
}
