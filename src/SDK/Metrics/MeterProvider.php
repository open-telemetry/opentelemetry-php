<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use Closure;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

interface MeterProvider extends \OpenTelemetry\API\Metrics\MeterProvider {

    public function registerView(
        SelectionCriteria $criteria,
        ?string $name = null,
        ?string $description = null,
        ?array $attributeKeys = null,
        ?Closure $aggregation = null,
        ?Closure $exemplarReservoir = null,
    ): void;

    public function shutdown(): bool;

    public function forceFlush(): bool;
}
