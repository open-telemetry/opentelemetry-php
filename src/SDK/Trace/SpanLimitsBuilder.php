<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\FilteredAttributesFactory;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SemConv\TraceAttributes;
use const PHP_INT_MAX;

class SpanLimitsBuilder
{
    use EnvironmentVariablesTrait;

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
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/semantic_conventions/span-general.md#general-identity-attributes
     */
    public function retainGeneralIdentityAttributes(bool $retain = true): SpanLimitsBuilder
    {
        $this->retainGeneralIdentityAttributes = $retain;

        return $this;
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#span-limits-
     */
    public function build(): SpanLimits
    {
        $attributeCountLimit = $this->attributeCountLimit
            ?: self::getIntFromEnvironment(Env::OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT, SpanLimits::DEFAULT_SPAN_ATTRIBUTE_COUNT_LIMIT);
        $attributeValueLengthLimit = $this->attributeValueLengthLimit
            ?: self::getIntFromEnvironment(Env::OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT, SpanLimits::DEFAULT_SPAN_ATTRIBUTE_LENGTH_LIMIT);
        $eventCountLimit = $this->eventCountLimit
            ?: self::getIntFromEnvironment(Env::OTEL_SPAN_EVENT_COUNT_LIMIT, SpanLimits::DEFAULT_SPAN_EVENT_COUNT_LIMIT);
        $linkCountLimit = $this->linkCountLimit
            ?: self::getIntFromEnvironment(Env::OTEL_SPAN_LINK_COUNT_LIMIT, SpanLimits::DEFAULT_SPAN_LINK_COUNT_LIMIT);
        $attributePerEventCountLimit = $this->attributePerEventCountLimit
            ?: self::getIntFromEnvironment(Env::OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT, SpanLimits::DEFAULT_EVENT_ATTRIBUTE_COUNT_LIMIT);
        $attributePerLinkCountLimit = $this->attributePerLinkCountLimit
            ?: self::getIntFromEnvironment(Env::OTEL_LINK_ATTRIBUTE_COUNT_LIMIT, SpanLimits::DEFAULT_LINK_ATTRIBUTE_COUNT_LIMIT);

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
