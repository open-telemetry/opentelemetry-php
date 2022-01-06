<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

/**
 * @internal
 */
final class AttributesFactory implements AttributesFactoryInterface
{
    private ?int $attributeCountLimit;
    private ?int $attributeValueLengthLimit;

    public function __construct(?int $attributeCountLimit = null, ?int $attributeValueLengthLimit = null)
    {
        $this->attributeCountLimit = $attributeCountLimit;
        $this->attributeValueLengthLimit = $attributeValueLengthLimit;
    }

    public function builder(iterable $attributes = []): AttributesBuilderInterface
    {
        return AttributesBuilder::from($attributes, $this->attributeCountLimit, $this->attributeValueLengthLimit);
    }
}
