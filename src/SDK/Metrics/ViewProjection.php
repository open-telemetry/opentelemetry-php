<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final class ViewProjection
{
    /**
     * `$includedAttributeKeys` and `$excludedAttributeKeys` are key patterns, where `?` matches one
     * character and `*` any sequence, and excluded wins. They are ignored if `$attributeKeys` is set.
     *
     * @param list<string>|null $attributeKeys
     * @param list<string>|null $includedAttributeKeys
     * @param list<string>|null $excludedAttributeKeys
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $unit,
        public readonly ?string $description,
        public readonly ?array $attributeKeys,
        public readonly ?AggregationInterface $aggregation,
        public readonly ?array $includedAttributeKeys = null,
        public readonly ?array $excludedAttributeKeys = null,
    ) {
    }
}
