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
    public function test_span_limits_builder_uses_global_attribute_limits(): void
    {
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', null);
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT', null);
        $this->setEnvironmentVariable('OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT', null);
        $this->setEnvironmentVariable('OTEL_LINK_ATTRIBUTE_COUNT_LIMIT', null);
        $this->setEnvironmentVariable('OTEL_ATTRIBUTE_COUNT_LIMIT', 111);
        $this->setEnvironmentVariable('OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT', 9);
        $spanLimits = (new SpanLimitsBuilder())->build();

        $this->assertEquals(Attributes::factory(111, 9), $spanLimits->getAttributesFactory());
        $this->assertEquals(Attributes::factory(111, 9), $spanLimits->getEventAttributesFactory());
        $this->assertEquals(Attributes::factory(111, 9), $spanLimits->getLinkAttributesFactory());
    }

    #[Group('trace-compliance')]
    public function test_span_limits_builder_prefers_span_attribute_limits(): void
    {
        $this->setEnvironmentVariable('OTEL_ATTRIBUTE_COUNT_LIMIT', 'invalid');
        $this->setEnvironmentVariable('OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT', 'invalid');
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT', 222);
        $this->setEnvironmentVariable('OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT', 17);
        $this->setEnvironmentVariable('OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT', 333);
        $this->setEnvironmentVariable('OTEL_LINK_ATTRIBUTE_COUNT_LIMIT', 444);
        $spanLimits = (new SpanLimitsBuilder())->build();

        $this->assertEquals(Attributes::factory(222, 17), $spanLimits->getAttributesFactory());
        $this->assertEquals(Attributes::factory(333, 17), $spanLimits->getEventAttributesFactory());
        $this->assertEquals(Attributes::factory(444, 17), $spanLimits->getLinkAttributesFactory());
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
}
