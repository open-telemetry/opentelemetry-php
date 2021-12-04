<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config;

class ResourceLimitsConfig implements ConfigInterface
{
    public ?int $attributeCount;
    public ?int $attributeValueCount;

    public function __construct(array $userConfig, array $environmentConfig)
    {
        $this->attributeCount = $this->intOrNull($userConfig['resource.limits.attribute_count'] ?? $environmentConfig['OTEL_ATTRIBUTE_COUNT_LIMIT'] ?? null);
        $this->attributeValueCount = (int) ($userConfig['resource.limits.attribute_value_length'] ?? $environmentConfig['OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT'] ?? 128);
    }

    private function intOrNull($value): ?int
    {
        return null === $value ? null : (int) $value;
    }
}
