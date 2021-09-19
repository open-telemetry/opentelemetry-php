<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use Exception;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\NonRecordingSpan;
use OpenTelemetry\Sdk\Trace\StatusCode;
use OpenTelemetry\Trace\SpanKind;
use PHPUnit\Framework\TestCase;

class NonRecordingSpanTest extends TestCase
{

    /**
     * @var NonRecordingSpan
     */
    private $span;

    public function setUp(): void
    {
        $this->span = new NonRecordingSpan();
    }

    /** @test */
    public function itShouldNotHaveANameEvenWhenNameIsUpdated()
    {
        $this->assertEmpty($this->span->getName());

        $this->span->updateName('some-custom-name');

        $this->assertEmpty($this->span->getName());
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

        $this->span->addEvent('event', null, (Clock::get())->timestamp());
        $this->assertEmpty($this->span->getEvents());
    }

    /** @test */
    public function eventsCollectionShouldBeEmptyEvenAfterRecordExceptionEventUpdate()
    {
        $this->assertEmpty($this->span->getEvents());

        try {
            throw new Exception('Record exception test event');
        } catch (Exception $exception) {
            $this->span->recordException($exception);
        }

        $this->assertEmpty($this->span->getEvents());
    }

    /** @test */
    public function itsStatusShouldBeOkAndNoUpdatesShouldChangeIt()
    {
        $this->assertFalse($this->span->isStatusOk());

        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $this->span->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $this->span->getCanonicalStatusCode());

        $this->span->setStatus(\OpenTelemetry\Trace\StatusCode::STATUS_ERROR);

        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $this->span->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $this->span->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());

        $this->span->setStatus(\OpenTelemetry\Trace\StatusCode::STATUS_OK);

        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $this->span->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $this->span->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());

        $this->span->setStatus('mycode');

        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $this->span->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $this->span->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());
    }

    /** @test */
    public function testGetStatusStaysSameAndNoUpdatesShouldChangeIt()
    {
        $status = $this->span->getStatus();
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $this->span->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $this->span->getCanonicalStatusCode());

        $this->span->setStatus(\OpenTelemetry\Trace\StatusCode::STATUS_ERROR);
        $status2 = $this->span->getStatus();

        self::assertEquals($status->getStatusDescription(), $status2->getStatusDescription());
        self::assertEquals($status->getCanonicalStatusCode(), $status2->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());

        $this->span->setStatus(\OpenTelemetry\Trace\StatusCode::STATUS_OK);
        $status2 = $this->span->getStatus();

        self::assertEquals($status->getStatusDescription(), $status2->getStatusDescription());
        self::assertEquals($status->getCanonicalStatusCode(), $status2->getCanonicalStatusCode());

        $this->span->setStatus('mycode');
        $status2 = $this->span->getStatus();

        self::assertEquals($status->getStatusDescription(), $status2->getStatusDescription());
        self::assertEquals($status->getCanonicalStatusCode(), $status2->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());
    }

    /** @test */
    public function itsSpanKindShouldBeInternal()
    {
        $this->assertEquals(SpanKind::KIND_INTERNAL, $this->span->getKind());
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
        $this->assertNull($this->span->getParentContext());
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
