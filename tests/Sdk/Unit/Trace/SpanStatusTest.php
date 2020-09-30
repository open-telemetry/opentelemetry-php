<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\SpanStatus;
use PHPUnit\Framework\TestCase;

class SpanStatusTest extends TestCase
{
    public function testGetCanonicalCode()
    {
        // todo: what's the point of SpanStatus::UNKNOWN if an unknown code gets propagated as some other code?
        $status = SpanStatus::new('MY_CODE');
        self::assertEquals('MY_CODE', $status->getCanonicalStatusCode());
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNKNOWN], $status->getStatusDescription());
    }

    public function testGetDescription()
    {
        $status = SpanStatus::new('MY_CODE', 'Neunundneunzig Luftballons');
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
