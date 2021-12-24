<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace;

use Exception;
use OpenTelemetry\API\AttributesInterface;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContextInterface;
use PHPUnit\Framework\TestCase;

class NonRecordingSpanTest extends TestCase
{
    private ?SpanContextInterface $context = null;

    public function testGetContext(): void
    {
        $span = $this->createNonRecordingSpan();

        $this->assertSame(
            $this->context,
            $span->getContext()
        );
    }

    public function testIsRecording(): void
    {
        $this->assertFalse(
            $this->createNonRecordingSpan()->isRecording()
        );
    }

    public function testSetAttribute(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->setAttribute('foo', 'bar')
        );
    }

    public function testSetAttributes(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->setAttributes(
                $this->createMock(AttributesInterface::class)
            )
        );
    }

    public function testAddEvent(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->addEvent('foo')
        );
    }

    public function testRecordException(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->recordException(
                new Exception()
            )
        );
    }

    public function testUpdateName(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->updateName('foo')
        );
    }

    public function testSetStatus(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->setStatus('Ok')
        );
    }

    public function testEnd(): void
    {
        $this->assertNull(
            // @phpstan-ignore-next-line
            $this->createNonRecordingSpan()->end()
        );
    }

    private function createNonRecordingSpan(): NonRecordingSpan
    {
        return new NonRecordingSpan(
            $this->getSpanContextInterfaceMock()
        );
    }

    private function getSpanContextInterfaceMock(): SpanContextInterface
    {
        return $this->context ?? $this->context = $this->createMock(SpanContextInterface::class);
    }
}
