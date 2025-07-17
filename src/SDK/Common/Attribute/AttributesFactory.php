<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

/**
 * @internal
 */
final class AttributesFactory implements AttributesFactoryInterface
{
    public function __construct(
        private readonly ?int $attributeCountLimit = null,
        private readonly ?int $attributeValueLengthLimit = null,
    ) {
    }

    #[\Override]
    public function builder(iterable $attributes = [], ?AttributeValidatorInterface $attributeValidator = null): AttributesBuilderInterface
    {
        $builder = new AttributesBuilder(
            [],
            $this->attributeCountLimit,
            $this->attributeValueLengthLimit,
            0,
            $attributeValidator ?? new AttributeValidator(),
        );
        foreach ($attributes as $key => $value) {
            $builder[$key] = $value;
        }

        return $builder;
    }
}
