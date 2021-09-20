<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

final class SpanLimits
{
    /** @var AttributeLimits */
    private $attributeLimits;

    private $eventCountLimit;

    private $linkCountLimit;

    private $attributePerEventCountLimit;

    private $attributePerLinkCountLimit;

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
