<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class SpanLimitsBuilderTest extends TestCase
{
    use TestState;

    /**
     * @group trace-compliance
     */
    public function test_span_length_limits_builder_uses_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT', 9);
        $builder = new SpanLimitsBuilder();
        $builder->retainGeneralIdentityAttributes();
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(128, 9), $spanLimits->getAttributesFactory());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_length_limits_builder_uses_configured_value(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT', 9);
        $builder = new SpanLimitsBuilder();
        $builder->retainGeneralIdentityAttributes();
        $builder->setAttributeValueLengthLimit(201);
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(128, 201), $spanLimits->getAttributesFactory());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_event_limits_builder_uses_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_EVENT_COUNT_LIMIT', 200);
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(200, $spanLimits->getEventCountLimit());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_event_limits_builder_uses_configured_value(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_EVENT_COUNT_LIMIT', 200);
        $builder = new SpanLimitsBuilder();
        $builder->setEventCountLimit(185);
        $spanLimits = $builder->build();
        $this->assertEquals(185, $spanLimits->getEventCountLimit());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_limits_link_builder_uses_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_LINK_COUNT_LIMIT', 1101);
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(1101, $spanLimits->getLinkCountLimit());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_limits_link_builder_uses_configured_value(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_LINK_COUNT_LIMIT', 1102);
        $builder = new SpanLimitsBuilder();
        $builder->setLinkCountLimit(193);
        $spanLimits = $builder->build();
        $this->assertEquals(193, $spanLimits->getLinkCountLimit());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_attribute_per_event_count_limits_builder_uses_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT', 400);
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(400), $spanLimits->getEventAttributesFactory());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_event_attribute_per_event_count_limits_builder_uses_configured_value(): void
    {
        $this->setEnvironmentVariable('OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT', 400);
        $builder = new SpanLimitsBuilder();
        $builder->setAttributePerEventCountLimit(155);
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(155), $spanLimits->getEventAttributesFactory());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_attribute_per_link_count_limits_builder_uses_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_LINK_ATTRIBUTE_COUNT_LIMIT', 500);
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(500), $spanLimits->getLinkAttributesFactory());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_link_attribute_per_event_count_limits_builder_uses_configured_value(): void
    {
        $this->setEnvironmentVariable('OTEL_LINK_ATTRIBUTE_COUNT_LIMIT', 500);
        $builder = new SpanLimitsBuilder();
        $builder->setAttributePerLinkCountLimit(450);
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(450), $spanLimits->getLinkAttributesFactory());
    }
}
