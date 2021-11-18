<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\API\Trace\AttributesInterface;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\NoopSpanBuilder;
use OpenTelemetry\Tests\SDK\Util\TestClock;
use PHPUnit\Framework\TestCase;

class NoopSpanBuilderTest extends TestCase
{
    public function testGetInstance(): void
    {
        $this->assertSame(
            NoopSpanBuilder::getInstance(),
            NoopSpanBuilder::getInstance()
        );
    }

    public function testSetParent(): void
    {
        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder())->setParent(
            // @todo: Create a interface for Context to allow it to be mocked
                new Context()
            )
        );
    }

    public function testSetNoParent(): void
    {
        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder())->setNoParent()
        );
    }

    public function testAddLink(): void
    {
        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder())->addLink(
                $this->createMock(SpanContextInterface::class)
            )
        );
    }

    public function testSetAttribute(): void
    {
        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder())->setAttribute('foo', 'bar')
        );
    }

    public function testSetAttributes(): void
    {
        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder())->setAttributes(
                $this->createMock(AttributesInterface::class)
            )
        );
    }

    public function testSetStartTimestamp(): void
    {
        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder())->setStartTimestamp(
                (new TestClock())->now()
            )
        );
    }

    public function testSetSpanKind(): void
    {
        $this->assertInstanceOf(
            NoopSpanBuilder::class,
            (new NoopSpanBuilder())->setSpanKind(1)
        );
    }

    public function testStartSpan(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            (new NoopSpanBuilder())->startSpan()
        );
    }
}
