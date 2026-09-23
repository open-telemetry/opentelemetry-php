<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Logs\LogRecordLimits;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogRecordLimitsBuilder::class)]
#[CoversClass(LogRecordLimits::class)]
class LogRecordLimitsBuilderTest extends TestCase
{
    use TestState;

    public function test_builder_uses_global_attribute_limits(): void
    {
        $this->setEnvironmentVariable('OTEL_LOGRECORD_ATTRIBUTE_COUNT_LIMIT', null);
        $this->setEnvironmentVariable('OTEL_LOGRECORD_ATTRIBUTE_VALUE_LENGTH_LIMIT', null);
        $this->setEnvironmentVariable('OTEL_ATTRIBUTE_COUNT_LIMIT', 111);
        $this->setEnvironmentVariable('OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT', 9);
        $limits = (new LogRecordLimitsBuilder())->build();

        $this->assertEquals(Attributes::factory(111, 9), $limits->getAttributeFactory());
    }

    public function test_builder_prefers_log_record_attribute_limits(): void
    {
        $this->setEnvironmentVariable('OTEL_ATTRIBUTE_COUNT_LIMIT', 'invalid');
        $this->setEnvironmentVariable('OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT', 'invalid');
        $this->setEnvironmentVariable('OTEL_LOGRECORD_ATTRIBUTE_COUNT_LIMIT', 222);
        $this->setEnvironmentVariable('OTEL_LOGRECORD_ATTRIBUTE_VALUE_LENGTH_LIMIT', 17);
        $limits = (new LogRecordLimitsBuilder())->build();

        $this->assertEquals(Attributes::factory(222, 17), $limits->getAttributeFactory());
    }

    public function test_builder(): void
    {
        $limits = (new LogRecordLimitsBuilder())
            ->setAttributeCountLimit(2)
            ->setAttributeValueLengthLimit(5)
            ->build();

        $attributes = $limits->getAttributeFactory()->builder([
            'foo' => 'bar', //allowed, <5 chars
            'long' => 'long-attribute-value', //trimmed, >5 chars
            'bar' => 'baz', //dropped, exceeds count
        ])->build();

        $this->assertSame(1, $attributes->getDroppedAttributesCount());
        $this->assertCount(2, $attributes);
        $this->assertSame('long-', $attributes->get('long'));
        $this->assertSame('bar', $attributes->get('foo'));
    }
}
