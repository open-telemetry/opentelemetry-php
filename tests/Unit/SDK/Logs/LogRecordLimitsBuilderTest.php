<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder
 * @covers \OpenTelemetry\SDK\Logs\LogRecordLimits
 */
class LogRecordLimitsBuilderTest extends TestCase
{
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
