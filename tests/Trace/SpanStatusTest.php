<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use PHPUnit\Framework\TestCase;

class SpanStatusTest extends TestCase
{
    public function testGetCanonicalCode()
    {
        $status = SpanStatus::new(99);
        $this->assertEquals(99, $status->getStatusDescription());
    }

    public function testGetDescription()
    {
        $status = SpanStatus::new(99, 'Neunundneunzig Luftballons');
        $this->assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
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
