<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Trace\SpanLimitsBuilder
 */
class SpanLimitsBuilderTest extends TestCase
{
    use EnvironmentVariables;

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
}
