<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use Exception;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanLimitsBuilder::class)]
class SpanLimitsBuilderTest extends TestCase
{
    use TestState;

    public function test_span_limits_builder_uses_default_values(): void
    {
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(128), $spanLimits->getAttributesFactory());
    }

    #[Group('trace-compliance')]
    public function test_span_limits_builder_uses_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 111);
        $builder = new SpanLimitsBuilder();
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(111), $spanLimits->getAttributesFactory());
    }

    #[Group('trace-compliance')]
    public function test_span_limits_builder_uses_configured_value(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 111);
        $builder = new SpanLimitsBuilder();
        $builder->setAttributeCountLimit(222);
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(222), $spanLimits->getAttributesFactory());
    }

    #[Group('trace-compliance')]
    public function test_span_limits_builder_throws_exception_on_invalid_value_from_environment(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 'fruit');
        $builder = new SpanLimitsBuilder();
        $this->expectException(Exception::class);
        $builder->build();
    }

    public function test_span_limits_builder_set_event_count_limit(): void
    {
        $builder = new SpanLimitsBuilder();
        $builder->setEventCountLimit(50);
        $spanLimits = $builder->build();
        $this->assertSame(50, $spanLimits->getEventCountLimit());
    }

    public function test_span_limits_builder_set_link_count_limit(): void
    {
        $builder = new SpanLimitsBuilder();
        $builder->setLinkCountLimit(64);
        $spanLimits = $builder->build();
        $this->assertSame(64, $spanLimits->getLinkCountLimit());
    }

    public function test_span_limits_builder_set_attribute_per_event_count_limit(): void
    {
        $builder = new SpanLimitsBuilder();
        $builder->setAttributePerEventCountLimit(32);
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(32), $spanLimits->getEventAttributesFactory());
    }

    public function test_span_limits_builder_set_attribute_per_link_count_limit(): void
    {
        $builder = new SpanLimitsBuilder();
        $builder->setAttributePerLinkCountLimit(48);
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(48), $spanLimits->getLinkAttributesFactory());
    }

    public function test_span_limits_builder_set_attribute_value_length_limit(): void
    {
        $builder = new SpanLimitsBuilder();
        $builder->setAttributeValueLengthLimit(100);
        $spanLimits = $builder->build();
        $this->assertEquals(Attributes::factory(128, 100), $spanLimits->getAttributesFactory());
    }
}
