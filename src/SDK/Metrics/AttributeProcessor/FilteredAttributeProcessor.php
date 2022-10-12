<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;

/**
 * @internal
 */
final class FilteredAttributeProcessor implements AttributeProcessorInterface
{
    private AttributesFactoryInterface $attributesFactory;
    private array $attributeKeys;

    public function __construct(AttributesFactoryInterface $attributesFactory, array $attributeKeys)
    {
        $this->attributesFactory = $attributesFactory;
        $this->attributeKeys = $attributeKeys;
    }

    public function process(AttributesInterface $attributes, ContextInterface $context): AttributesInterface
    {
        $filtered = $this->attributesFactory->builder();
        foreach ($this->attributeKeys as $key) {
            $filtered[$key] = $attributes->get($key);
        }

        return $filtered->build();
    }
}
