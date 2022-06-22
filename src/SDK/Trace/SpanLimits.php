<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Attribute\AttributeLimits;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;

final class SpanLimits
{
    public const DEFAULT_SPAN_ATTRIBUTE_LENGTH_LIMIT = PHP_INT_MAX;
    public const DEFAULT_SPAN_ATTRIBUTE_COUNT_LIMIT = 128;
    public const DEFAULT_SPAN_EVENT_COUNT_LIMIT = 128;
    public const DEFAULT_SPAN_LINK_COUNT_LIMIT = 128;
    public const DEFAULT_EVENT_ATTRIBUTE_COUNT_LIMIT = 128;
    public const DEFAULT_LINK_ATTRIBUTE_COUNT_LIMIT = 128;

    private AttributeLimits $attributeLimits;

    private int $eventCountLimit;

    private int $linkCountLimit;

    private int $attributePerEventCountLimit;

    private int $attributePerLinkCountLimit;

    public function getAttributesFactory(): AttributesFactoryInterface
    {
        return Attributes::factory($this->attributeLimits->getAttributeCountLimit(), $this->attributeLimits->getAttributeValueLengthLimit());
    }

    public function getEventAttributesFactory(): AttributesFactoryInterface
    {
        return Attributes::factory($this->attributePerEventCountLimit, $this->attributeLimits->getAttributeValueLengthLimit());
    }

    public function getLinkAttributesFactory(): AttributesFactoryInterface
    {
        return Attributes::factory($this->attributePerLinkCountLimit, $this->attributeLimits->getAttributeValueLengthLimit());
    }

    public function getAttributeLimits(): AttributeLimits
    {
        return $this->attributeLimits;
    }

    /** @return int Maximum allowed span event count */
    public function getEventCountLimit(): int
    {
        return $this->eventCountLimit;
    }

    /** @return int Maximum allowed span link count */
    public function getLinkCountLimit(): int
    {
        return $this->linkCountLimit;
    }

    /** @return int Maximum allowed attribute per span event count */
    public function getAttributePerEventCountLimit(): int
    {
        return $this->attributePerEventCountLimit;
    }

    /** @return int Maximum allowed attribute per span link count */
    public function getAttributePerLinkCountLimit(): int
    {
        return $this->attributePerLinkCountLimit;
    }

    /**
     * @internal Use {@see SpanLimitsBuilder} to create {@see SpanLimits} instance.
     */
    public function __construct(
        int $attributeCountLimit,
        int $attributeValueLengthLimit,
        int $eventCountLimit,
        int $linkCountLimit,
        int $attributePerEventCountLimit,
        int $attributePerLinkCountLimit
    ) {
        $this->attributeLimits = new AttributeLimits($attributeCountLimit, $attributeValueLengthLimit);
        $this->eventCountLimit = $eventCountLimit;
        $this->linkCountLimit = $linkCountLimit;
        $this->attributePerEventCountLimit = $attributePerEventCountLimit;
        $this->attributePerLinkCountLimit = $attributePerLinkCountLimit;
    }
}
