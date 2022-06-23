<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Trace\SpanLimitsBuilder
 */
class SpanLimitsBuilderTest extends TestCase
{
    use EnvironmentVariables;

    public function test_span_limits_builder_uses_default_values(): void
    {
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(128), $spanLimits->getAttributesFactory());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_limits_builder_uses_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 111);
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(111), $spanLimits->getAttributesFactory());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_limits_builder_uses_configured_value(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 111);
        $builder = new SpanLimitsBuilder();
        $builder->setAttributeCountLimit(222);
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(222), $spanLimits->getAttributesFactory());
    }

    /**
     * @group trace-compliance
     */
    public function test_span_limits_builder_throws_exception_on_invalid_value_from_environment(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 'fruit');
        $builder = new SpanLimitsBuilder();
        $this->expectException(Exception::class);
        $builder->build();
    }
}
