<?php

declare(strict_types=1);

namespace Trace;

use OpenTelemetry\Trace\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function testGetCanonicalCode()
    {
        $status = Status::new(99);
        $this->assertEquals(99, $status->getCanonicalCode());
    }

    public function testGetDescription()
    {
        $status = Status::new(99, 'Neunundneunzig Luftballons');
        $this->assertEquals('Neunundneunzig Luftballons', $status->getDescription());
    }

    public function testIsOKReturnsTrueForOkStatus()
    {
        $status = Status::new(Status::OK);
        $this->assertTrue($status->isOK());
    }

    public function testIsOKReturnsFalseForNonOkStatus()
    {
        $status = Status::new(Status::ABORTED);
        $this->assertFalse($status->isOK());
    }

    public function testOkReturnsOkStatus(): void
    {
        $okStatus = Status::ok();
        $this->assertTrue($okStatus->isOK());
    }

    public function testNewUsesCachedStatuses(): void
    {
        $status = Status::new(Status::OK);
        $status2 = Status::new(Status::OK);
        $this->assertSame($status, $status2);
    }

    public function testNewDoesNotCacheDifferentDescriptions(): void
    {
        $status = Status::new(Status::OK);
        $status2 = Status::new(Status::OK, 'Okay!');
        $this->assertNotSame($status, $status2);
    }
}
