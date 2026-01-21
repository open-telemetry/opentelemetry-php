<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final readonly class ViewProjection
{
    /**
     * @param list<string>|null $attributeKeys
     */
    public function __construct(
        public string $name,
        public ?string $unit,
        public ?string $description,
        public ?array $attributeKeys,
        public ?AggregationInterface $aggregation,
    ) {
    }
}
