<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

class AttributeLimits
{
    private const DEFAULT_COUNT_LIMIT = 128;

    private const DEFAULT_VALUE_LENGTH_LIMIT = PHP_INT_MAX;

    private int $attributeCountLimit;

    private int $attributeValueLengthLimit;

    public function __construct(
        int $attributeCountLimit = self::DEFAULT_COUNT_LIMIT,
        int $attributeValueLengthLimit = self::DEFAULT_VALUE_LENGTH_LIMIT
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
