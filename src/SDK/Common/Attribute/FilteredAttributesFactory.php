<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

/**
 * @internal
 */
final class FilteredAttributesFactory implements AttributesFactoryInterface
{
    private AttributesFactoryInterface $factory;
    private array $rejectedKeys;

    /**
     * @param list<string> $rejectedKeys
     */
    public function __construct(AttributesFactoryInterface $factory, array $rejectedKeys)
    {
        $this->factory = $factory;
        $this->rejectedKeys = $rejectedKeys;
    }

    public function builder(iterable $attributes = [], ?AttributeValidatorInterface $attributeValidator = null): AttributesBuilderInterface
    {
        $builder = new FilteredAttributesBuilder($this->factory->builder([], $attributeValidator), $this->rejectedKeys);
        foreach ($attributes as $attribute => $value) {
            $builder[$attribute] = $value;
        }

        return $builder;
    }
}
