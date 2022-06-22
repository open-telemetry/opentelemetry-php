<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;

final class SpanLimits
{
    public const DEFAULT_SPAN_ATTRIBUTE_LENGTH_LIMIT = PHP_INT_MAX;
    public const DEFAULT_SPAN_ATTRIBUTE_COUNT_LIMIT = 128;
    public const DEFAULT_SPAN_EVENT_COUNT_LIMIT = 128;
    public const DEFAULT_SPAN_LINK_COUNT_LIMIT = 128;
    public const DEFAULT_EVENT_ATTRIBUTE_COUNT_LIMIT = 128;
    public const DEFAULT_LINK_ATTRIBUTE_COUNT_LIMIT = 128;

    private AttributesFactoryInterface $attributesFactory;
    private AttributesFactoryInterface $eventAttributesFactory;
    private AttributesFactoryInterface $linkAttributesFactory;
    private int $eventCountLimit;
    private int $linkCountLimit;

    public function getAttributesFactory(): AttributesFactoryInterface
    {
        return $this->attributesFactory;
    }

    public function getEventAttributesFactory(): AttributesFactoryInterface
    {
        return $this->eventAttributesFactory;
    }

    public function getLinkAttributesFactory(): AttributesFactoryInterface
    {
        return $this->linkAttributesFactory;
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
        AttributesFactoryInterface $attributesFactory,
        AttributesFactoryInterface $eventAttributesFactory,
        AttributesFactoryInterface $linkAttributesFactory,
        int $eventCountLimit,
        int $linkCountLimit
    ) {
        $this->attributesFactory = $attributesFactory;
        $this->eventAttributesFactory = $eventAttributesFactory;
        $this->linkAttributesFactory = $linkAttributesFactory;
        $this->eventCountLimit = $eventCountLimit;
        $this->linkCountLimit = $linkCountLimit;
    }
}
