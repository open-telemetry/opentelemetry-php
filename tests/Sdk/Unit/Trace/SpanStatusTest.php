<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\SpanStatus;
use PHPUnit\Framework\TestCase;

class SpanStatusTest extends TestCase
{
    public function testGetCanonicalCodeAfterFunctionNew()
    {
        // If an invalid code is given, SpanStatus should be/remain UNSET.
        $status = SpanStatus::new('MY_CODE');
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());

        $status = SpanStatus::new(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::OK);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
    }

    public function testGetDescriptionAfterFunctionNew()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */
        $status = SpanStatus::new(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
    }

    public function testGetCanonicalCode()
    {
        // If an invalid code is given, SpanStatus should be/remain UNSET.
        $status = new SpanStatus('MY_CODE');
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());

        $status = new SpanStatus(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::OK);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
    }

    public function testGetDescription()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */
        $status = new SpanStatus(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
    }

    public function testSetStatusWithoutDescription()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */

        $status = new SpanStatus(SpanStatus::OK);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::OK);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
    }

    public function testSetStatusWithDescription()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */
        $status = new SpanStatus(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
    }

    public function testIsOKReturnsTrueForOkStatus()
    {
        $status = SpanStatus::new(SpanStatus::OK);
        $this->assertTrue($status->isStatusOK());
    }

    public function testIsOKReturnsFalseForNonOkStatus()
    {
        $status = SpanStatus::new();
        $this->assertFalse($status->isStatusOK());
    }

    public function testOkReturnsOkStatus(): void
    {
        $okStatus = SpanStatus::ok();
        $this->assertTrue($okStatus->isStatusOK());
    }

    public function testNewReturnsSameStatuses(): void
    {
        $status = SpanStatus::new(SpanStatus::OK);
        $status2 = SpanStatus::new(SpanStatus::OK);
        $this->assertSame($status->getCanonicalStatusCode(), $status2->getCanonicalStatusCode());
        $this->assertSame($status->getStatusDescription(), $status2->getStatusDescription());
    }

    public function testNewDoesNotCacheDifferentDescriptions(): void
    {
        $status = SpanStatus::new(SpanStatus::ERROR);
        $status2 = SpanStatus::new(SpanStatus::ERROR, 'Okay!');
        $this->assertNotSame($status, $status2);
    }
}
