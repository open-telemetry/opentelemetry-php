<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesFactoryInterface;

final class SpanLimits
{
    public const DEFAULT_SPAN_ATTRIBUTE_LENGTH_LIMIT = PHP_INT_MAX;
    public const DEFAULT_SPAN_ATTRIBUTE_COUNT_LIMIT = 128;
    public const DEFAULT_SPAN_EVENT_COUNT_LIMIT = 128;
    public const DEFAULT_SPAN_LINK_COUNT_LIMIT = 128;
    public const DEFAULT_EVENT_ATTRIBUTE_COUNT_LIMIT = 128;
    public const DEFAULT_LINK_ATTRIBUTE_COUNT_LIMIT = 128;

    private AttributesFactoryInterface $spanAttributesFactory;
    private AttributesFactoryInterface $linkAttributesFactory;
    private AttributesFactoryInterface $eventAttributesFactory;
    private int $eventCountLimit;
    private int $linkCountLimit;

    public function getSpanAttributesFactory(): AttributesFactoryInterface
    {
        return $this->spanAttributesFactory;
    }

    public function getLinkAttributesFactory(): AttributesFactoryInterface
    {
        return $this->linkAttributesFactory;
    }

    public function getEventAttributesFactory(): AttributesFactoryInterface
    {
        return $this->eventAttributesFactory;
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
        $this->spanAttributesFactory = Attributes::factory($attributeCountLimit, $attributeValueLengthLimit);
        $this->linkAttributesFactory = Attributes::factory($attributePerLinkCountLimit, $attributeValueLengthLimit);
        $this->eventAttributesFactory = Attributes::factory($attributePerEventCountLimit, $attributeValueLengthLimit);
        $this->eventCountLimit = $eventCountLimit;
        $this->linkCountLimit = $linkCountLimit;
    }
}
