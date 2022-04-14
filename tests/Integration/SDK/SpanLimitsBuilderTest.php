<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class SpanLimitsBuilderTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @group trace-compliance
     */
    public function test_span_length_limits_builder_uses_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT', 9);
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(9, $spanLimits->getAttributeLimits()->getAttributeValueLengthLimit());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_length_limits_builder_uses_configured_value(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT', 9);
        $builder = new SpanLimitsBuilder();
        $builder->setAttributeValueLengthLimit(201);
        $spanLimits = $builder->build();
        $this->assertEquals(201, $spanLimits->getAttributeLimits()->getAttributeValueLengthLimit());
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
        $this->assertEquals(400, $spanLimits->getAttributePerEventCountLimit());
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
        $this->assertEquals(155, $spanLimits->getAttributePerEventCountLimit());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_attribute_per_link_count_limits_builder_uses_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_LINK_ATTRIBUTE_COUNT_LIMIT', 500);
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(500, $spanLimits->getAttributePerLinkCountLimit());
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
        $this->assertEquals(450, $spanLimits->getAttributePerLinkCountLimit());
    }
}
