<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final class ViewProjection
{
    /**
     * @param list<string>|null $attributeKeys
     */
    public function __construct(
        /** @readonly */
        public string $name,
        /** @readonly */
        public ?string $unit,
        /** @readonly */
        public ?string $description,
        /** @readonly */
        public ?array $attributeKeys,
        /** @readonly */
        public ?AggregationInterface $aggregation
    ) {
    }
}
