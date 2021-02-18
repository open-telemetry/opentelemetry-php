<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use Exception;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\NoopSpan;
use OpenTelemetry\Sdk\Trace\SpanStatus;
use OpenTelemetry\Trace\SpanKind;
use PHPUnit\Framework\TestCase;

class NoopSpanTest extends TestCase
{

    /**
     * @var NoopSpan
     */
    private $span;

    public function setUp(): void
    {
        $this->span = new NoopSpan();
    }

    /** @test */
    public function itShouldNotHaveANameEvenWhenNameIsUpdated()
    {
        $this->assertEmpty($this->span->getSpanName());

        $this->span->updateName('some-custom-name');

        $this->assertEmpty($this->span->getSpanName());
    }

    /** @test */
    public function attributesCollectionShouldBeEmptyEvenAfterUpdate()
    {
        $this->assertEmpty($this->span->getAttributes());

        $this->span->setAttribute('key', 'value');
        $this->assertEmpty($this->span->getAttributes());
    }

    /** @test */
    public function eventsCollectionShouldBeEmptyEvenAfterUpdate()
    {
        $this->assertEmpty($this->span->getEvents());

        $this->span->addEvent('event', (Clock::get())->timestamp());
        $this->assertEmpty($this->span->getEvents());
    }

    /** @test */
    public function eventsCollectionShouldBeEmptyEvenAfterRecordExceptionEventUpdate()
    {
        $this->assertEmpty($this->span->getEvents());
        $firstInput = 1;
        $secondInput = 0;
        
        try {
            // @phpstan-ignore-next-line
            $firstInput / $secondInput;
        } catch (Exception $exception) {
            $this->span->recordException($exception);
        }

        $this->assertEmpty($this->span->getEvents());
    }

    /** @test */
    public function itsStatusShouldBeOkAndNoUpdatesShouldChangeIt()
    {
        $this->assertEquals(
            SpanStatus::ok(),
            $this->span->getStatus()
        );

        $this->assertTrue($this->span->isStatusOk());

        $this->assertEquals(
            SpanStatus::ok()->getStatusDescription(),
            $this->span->getStatusDescription()
        );

        $this->assertEquals(
            SpanStatus::ok()->getCanonicalStatusCode(),
            $this->span->getCanonicalStatusCode()
        );

        $this->span->setSpanStatus(\OpenTelemetry\Trace\SpanStatus::ABORTED);

        $this->assertEquals(
            SpanStatus::ok(),
            $this->span->getStatus()
        );

        $this->assertTrue($this->span->isStatusOk());
    }

    /** @test */
    public function itsSpanKindShouldBeInternal()
    {
        $this->assertEquals(SpanKind::KIND_INTERNAL, $this->span->getSpanKind());
    }

    /** @test */
    public function itShouldNeverRecord()
    {
        $this->assertFalse($this->span->isRecording());
    }

    /** @test */
    public function itShouldNeverBeSampled()
    {
        $this->assertFalse($this->span->isSampled());
    }

    /** @test */
    public function itShouldNotHaveAParent()
    {
        $this->assertNull($this->span->getParent());
    }

    /** @test */
    public function itShouldHaveAnInvalidSpanContext()
    {
        $this->assertFalse($this->span->getContext()->isValid());
    }

    /** @test */
    public function itShouldNotBeTimeBounded()
    {
        $this->assertEmpty($this->span->getStart());
        $this->assertEmpty($this->span->getEnd());
        $this->assertEmpty($this->span->getStartEpochTimestamp());

        $this->span->end((Clock::get())->timestamp());

        $this->assertEmpty($this->span->getEnd());
    }
}
