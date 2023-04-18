<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;

/**
 * @internal
 */
final class FilteredAttributeProcessor implements AttributeProcessorInterface
{
    private array $attributeKeys;

    public function __construct(array $attributeKeys)
    {
        $this->attributeKeys = $attributeKeys;
    }

    public function process(AttributesInterface $attributes, ContextInterface $context): AttributesInterface
    {
        $filtered = [];
        foreach ($this->attributeKeys as $key) {
            $filtered[$key] = $attributes->get($key);
        }

        return new Attributes($filtered, 0);
    }
}
