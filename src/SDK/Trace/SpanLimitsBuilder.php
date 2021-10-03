<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

class SpanLimitsBuilder
{
    /** @var int Maximum allowed attribute count per record */
    private $attributeCountLimit = 128;

    /** @var int Maximum allowed attribute value length */
    private $attributeValueLengthLimit = PHP_INT_MAX;

    /** @var int Maximum allowed span event count */
    private $eventCountLimit = 128;

    /** @var int Maximum allowed span link count */
    private $linkCountLimit = 128;

    /** @var int Maximum allowed attribute per span event count */
    private $attributePerEventCountLimit = 128;

    /** @var int Maximum allowed attribute per span link count */
    private $attributePerLinkCountLimit = 128;

    /**
     * @param int $attributeCountLimit Maximum allowed attribute count per record
     */
    public function setAttributeCountLimit(int $attributeCountLimit): SpanLimitsBuilder
    {
        $this->attributeCountLimit = $attributeCountLimit;

        return $this;
    }

    /**
     * @param int $attributeValueLengthLimit Maximum allowed attribute value length
     */
    public function setAttributeValueLengthLimit(int $attributeValueLengthLimit): SpanLimitsBuilder
    {
        $this->attributeValueLengthLimit = $attributeValueLengthLimit;

        return $this;
    }

    /**
     * @param int $eventCountLimit Maximum allowed span event count
     */
    public function setEventCountLimit(int $eventCountLimit): SpanLimitsBuilder
    {
        $this->eventCountLimit = $eventCountLimit;

        return $this;
    }

    /**
     * @param int $linkCountLimit Maximum allowed span link count
     */
    public function setLinkCountLimit(int $linkCountLimit): SpanLimitsBuilder
    {
        $this->linkCountLimit = $linkCountLimit;

        return $this;
    }

    /**
     * @param int $attributePerEventCountLimit Maximum allowed attribute per span event count
     */
    public function setAttributePerEventCountLimit(int $attributePerEventCountLimit): SpanLimitsBuilder
    {
        $this->attributePerEventCountLimit = $attributePerEventCountLimit;

        return $this;
    }

    /**
     * @param int $attributePerLinkCountLimit Maximum allowed attribute per span link count
     */
    public function setAttributePerLinkCountLimit(int $attributePerLinkCountLimit): SpanLimitsBuilder
    {
        $this->attributePerLinkCountLimit = $attributePerLinkCountLimit;

        return $this;
    }

    public function build(): SpanLimits
    {
        return new SpanLimits(
            $this->attributeCountLimit,
            $this->attributeValueLengthLimit,
            $this->eventCountLimit,
            $this->linkCountLimit,
            $this->attributePerEventCountLimit,
            $this->attributePerLinkCountLimit
        );
    }
}
