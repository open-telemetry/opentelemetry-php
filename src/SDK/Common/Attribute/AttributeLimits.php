<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

final class AttributeLimits implements AttributeLimitsInterface
{
    private int $attributeCountLimit;

    private int $attributeValueLengthLimit;

    public function __construct(
        int $attributeCountLimit = AttributeLimitsInterface::DEFAULT_COUNT_LIMIT,
        int $attributeValueLengthLimit = AttributeLimitsInterface::DEFAULT_VALUE_LENGTH_LIMIT
    ) {
        $this->attributeCountLimit = $attributeCountLimit;
        $this->attributeValueLengthLimit = $attributeValueLengthLimit;
    }

    /** @return int Maximum allowed attribute count */
    public function getAttributeCountLimit(): int
    {
        return $this->attributeCountLimit;
    }

    /** @return int Maximum allowed attribute value length */
    public function getAttributeValueLengthLimit(): int
    {
        return $this->attributeValueLengthLimit;
    }
}
