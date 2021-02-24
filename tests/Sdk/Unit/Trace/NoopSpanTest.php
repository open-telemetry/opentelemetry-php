<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

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
    public function itsStatusShouldBeUnsetAndNoUpdatesShouldChangeIt()
    {
        $this->assertFalse($this->span->isStatusOk());

        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $this->span->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $this->span->getCanonicalStatusCode());

        $this->span->setSpanStatus(\OpenTelemetry\Trace\SpanStatus::ERROR);

        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $this->span->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $this->span->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());

        $this->span->setSpanStatus(\OpenTelemetry\Trace\SpanStatus::OK);

        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $this->span->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $this->span->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());

        $this->span->setSpanStatus('mycode');

        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $this->span->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $this->span->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());
    }

    /** @test */
    public function testGetStatusStaysSameAndNoUpdatesShouldChangeIt()
    {
        $status = $this->span->getStatus();
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $this->span->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $this->span->getCanonicalStatusCode());

        $this->span->setSpanStatus(\OpenTelemetry\Trace\SpanStatus::ERROR);
        $status2 = $this->span->getStatus();

        self::assertEquals($status->getStatusDescription(), $status2->getStatusDescription());
        self::assertEquals($status->getCanonicalStatusCode(), $status2->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());

        $this->span->setSpanStatus(\OpenTelemetry\Trace\SpanStatus::OK);
        $status2 = $this->span->getStatus();

        self::assertEquals($status->getStatusDescription(), $status2->getStatusDescription());
        self::assertEquals($status->getCanonicalStatusCode(), $status2->getCanonicalStatusCode());

        $this->span->setSpanStatus('mycode');
        $status2 = $this->span->getStatus();

        self::assertEquals($status->getStatusDescription(), $status2->getStatusDescription());
        self::assertEquals($status->getCanonicalStatusCode(), $status2->getCanonicalStatusCode());

        $this->assertFalse($this->span->isStatusOk());
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
