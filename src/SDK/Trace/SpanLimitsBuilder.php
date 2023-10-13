<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\FilteredAttributesFactory;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SemConv\TraceAttributes;
use const PHP_INT_MAX;

class SpanLimitsBuilder
{
    /** @var ?int Maximum allowed attribute count per record */
    private ?int $attributeCountLimit = null;

    /** @var ?int Maximum allowed attribute value length */
    private ?int $attributeValueLengthLimit = null;

    /** @var ?int Maximum allowed span event count */
    private ?int $eventCountLimit = null;

    /** @var ?int Maximum allowed span link count */
    private ?int $linkCountLimit = null;

    /** @var ?int Maximum allowed attribute per span event count */
    private ?int $attributePerEventCountLimit = null;

    /** @var ?int Maximum allowed attribute per span link count */
    private ?int $attributePerLinkCountLimit = null;

    private bool $retainGeneralIdentityAttributes = false;

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

    /**
     * @param bool $retain whether general identity attributes should be retained
     *
     * @see https://github.com/open-telemetry/semantic-conventions/blob/main/docs/general/attributes.md#general-identity-attributes
     */
    public function retainGeneralIdentityAttributes(bool $retain = true): SpanLimitsBuilder
    {
        $this->retainGeneralIdentityAttributes = $retain;

        return $this;
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#span-limits
     */
    public function build(): SpanLimits
    {
        $attributeCountLimit = $this->attributeCountLimit
            ?: Configuration::getInt(Env::OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT, SpanLimits::DEFAULT_SPAN_ATTRIBUTE_COUNT_LIMIT);
        $attributeValueLengthLimit = $this->attributeValueLengthLimit
            ?: Configuration::getInt(Env::OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT, SpanLimits::DEFAULT_SPAN_ATTRIBUTE_LENGTH_LIMIT);
        $eventCountLimit = $this->eventCountLimit
            ?: Configuration::getInt(Env::OTEL_SPAN_EVENT_COUNT_LIMIT, SpanLimits::DEFAULT_SPAN_EVENT_COUNT_LIMIT);
        $linkCountLimit = $this->linkCountLimit
            ?: Configuration::getInt(Env::OTEL_SPAN_LINK_COUNT_LIMIT, SpanLimits::DEFAULT_SPAN_LINK_COUNT_LIMIT);
        $attributePerEventCountLimit = $this->attributePerEventCountLimit
            ?: Configuration::getInt(Env::OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT, SpanLimits::DEFAULT_EVENT_ATTRIBUTE_COUNT_LIMIT);
        $attributePerLinkCountLimit = $this->attributePerLinkCountLimit
            ?: Configuration::getInt(Env::OTEL_LINK_ATTRIBUTE_COUNT_LIMIT, SpanLimits::DEFAULT_LINK_ATTRIBUTE_COUNT_LIMIT);

        if ($attributeValueLengthLimit === PHP_INT_MAX) {
            $attributeValueLengthLimit = null;
        }

        $spanAttributesFactory = Attributes::factory($attributeCountLimit, $attributeValueLengthLimit);

        if (!$this->retainGeneralIdentityAttributes) {
            $spanAttributesFactory = new FilteredAttributesFactory($spanAttributesFactory, [
                TraceAttributes::ENDUSER_ID,
                TraceAttributes::ENDUSER_ROLE,
                TraceAttributes::ENDUSER_SCOPE,
            ]);
        }

        return new SpanLimits(
            $spanAttributesFactory,
            Attributes::factory($attributePerEventCountLimit, $attributeValueLengthLimit),
            Attributes::factory($attributePerLinkCountLimit, $attributeValueLengthLimit),
            $eventCountLimit,
            $linkCountLimit,
        );
    }
}
