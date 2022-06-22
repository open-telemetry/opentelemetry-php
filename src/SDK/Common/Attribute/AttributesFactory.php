<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

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
        $builder = new AttributesBuilder(
            [],
            $this->attributeCountLimit,
            $this->attributeValueLengthLimit,
            0,
        );
        foreach ($attributes as $key => $value) {
            $builder[$key] = $value;
        }

        return $builder;
    }
}
