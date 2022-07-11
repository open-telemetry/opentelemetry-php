<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

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
     * @var list<string>|null
     */
    public ?array $attributeKeys;
    /**
     * @readonly
     */
    public ?AggregationInterface $aggregation;

    /**
     * @param list<string>|null $attributeKeys
     */
    public function __construct(
        string $name,
        ?string $unit,
        ?string $description,
        ?array $attributeKeys,
        ?AggregationInterface $aggregation
    ) {
        $this->name = $name;
        $this->unit = $unit;
        $this->description = $description;
        $this->attributeKeys = $attributeKeys;
        $this->aggregation = $aggregation;
    }
}
