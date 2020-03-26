<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests\Trace;

use OpenTelemetry\Sdk\Trace\SpanStatus;

class SpanStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testGetCanonicalCode()
    {
        // todo: what's the point of SpanStatus::UNKNOWN if an unknown code gets propagated as some other code?
        $status = SpanStatus::new(99);
        self::assertEquals(99, $status->getCanonicalStatusCode());
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNKNOWN], $status->getStatusDescription());
    }

    public function testGetDescription()
    {
        $status = SpanStatus::new(99, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
    }

    public function testIsOKReturnsTrueForOkStatus()
    {
        $status = SpanStatus::new(SpanStatus::OK);
        $this->assertTrue($status->isStatusOK());
    }

    public function testIsOKReturnsFalseForNonOkStatus()
    {
        $status = SpanStatus::new(SpanStatus::ABORTED);
        $this->assertFalse($status->isStatusOK());
    }

    public function testOkReturnsOkStatus(): void
    {
        $okStatus = SpanStatus::ok();
        $this->assertTrue($okStatus->isStatusOK());
    }

    public function testNewUsesCachedStatuses(): void
    {
        $status = SpanStatus::new(SpanStatus::OK);
        $status2 = SpanStatus::new(SpanStatus::OK);
        $this->assertSame($status, $status2);
    }

    public function testNewDoesNotCacheDifferentDescriptions(): void
    {
        $status = SpanStatus::new(SpanStatus::OK);
        $status2 = SpanStatus::new(SpanStatus::OK, 'Okay!');
        $this->assertNotSame($status, $status2);
    }
}
