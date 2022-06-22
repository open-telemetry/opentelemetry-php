<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Link;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Trace\Link
 */
class LinkTest extends TestCase
{
    private API\SpanContextInterface $context;
    private AttributesInterface $attributes;

    public function setUp(): void
    {
        $this->context = $this->createMock(API\SpanContextInterface::class);
        $this->attributes = $this->createMock(AttributesInterface::class);
        $this->attributes->method('count')->willReturn(5);
    }

    public function test_getters(): void
    {
        $link = new Link($this->context, $this->attributes);

        $this->assertSame($this->context, $link->getSpanContext());
        $this->assertSame($this->attributes, $link->getAttributes());
    }
}
