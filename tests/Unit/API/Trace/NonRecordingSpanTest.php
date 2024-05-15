<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace;

use Exception;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NonRecordingSpan::class)]
class NonRecordingSpanTest extends TestCase
{
    private ?SpanContextInterface $context = null;

    public function test_get_context(): void
    {
        $span = $this->createNonRecordingSpan();

        $this->assertSame(
            $this->context,
            $span->getContext()
        );
    }

    public function test_is_recording(): void
    {
        $this->assertFalse(
            $this->createNonRecordingSpan()->isRecording()
        );
    }

    public function test_set_attribute(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->setAttribute('foo', 'bar')
        );
    }

    public function test_set_attributes(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->setAttributes(
                $this->createMock(AttributesInterface::class)
            )
        );
    }

    public function test_add_event(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->addEvent('foo')
        );
    }

    public function test_record_exception(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->recordException(
                new Exception()
            )
        );
    }

    public function test_update_name(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->updateName('foo')
        );
    }

    public function test_set_status(): void
    {
        $this->assertInstanceOf(
            NonRecordingSpan::class,
            $this->createNonRecordingSpan()->setStatus('Ok')
        );
    }

    public function test_end(): void
    {
        // @phpstan-ignore-next-line
        $this->assertNull($this->createNonRecordingSpan()->end());
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
