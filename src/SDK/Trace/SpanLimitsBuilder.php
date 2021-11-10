<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

class SpanLimitsBuilder
{
    /** @var int Maximum allowed attribute count per record */
    private $attributeCountLimit;

    /** @var int Maximum allowed attribute value length */
    private $attributeValueLengthLimit;

    /** @var int Maximum allowed span event count */
    private $eventCountLimit;

    /** @var int Maximum allowed span link count */
    private $linkCountLimit;

    /** @var int Maximum allowed attribute per span event count */
    private $attributePerEventCountLimit;

    /** @var int Maximum allowed attribute per span link count */
    private $attributePerLinkCountLimit;

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
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#span-limits-
     */
    public function build(): SpanLimits
    {
        return new SpanLimits(
            $this->attributeCountLimit ?: $this->fromEnv('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 128),
            $this->attributeValueLengthLimit ?: $this->fromEnv('OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT', PHP_INT_MAX),
            $this->eventCountLimit ?: $this->fromEnv('OTEL_SPAN_EVENT_COUNT_LIMIT', 128),
            $this->linkCountLimit ?: $this->fromEnv('OTEL_SPAN_LINK_COUNT_LIMIT', 128),
            $this->attributePerEventCountLimit ?: $this->fromEnv('OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT', 128),
            $this->attributePerLinkCountLimit ?: $this->fromEnv('OTEL_LINK_ATTRIBUTE_COUNT_LIMIT', 128),
        );
    }

    private function fromEnv(string $key, int $default): int
    {
        $value = getenv($key);
        if (false === $value) {
            return $default;
        }
        if (!is_numeric($value)) {
            throw new \Exception($key . ' contains non-numeric value');
        }

        return (int) $value;
    }
}
