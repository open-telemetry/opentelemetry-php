<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\StatusCode;
use PHPUnit\Framework\TestCase;

class SpanStatusTest extends TestCase
{
    public function testGetCanonicalCode()
    {
        // If an invalid code is given, SpanStatus should be/remain UNSET.
        $status = StatusCode::new('MY_CODE');
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());

        $status = StatusCode::new(StatusCode::UNSET);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::OK);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        // Using new SpanStatus
        // If an invalid code is given, SpanStatus should be/remain UNSET.
        $status = new StatusCode('MY_CODE');
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());

        $status = new StatusCode(StatusCode::UNSET);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::OK);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
    }

    public function testNoParamsToFunctionNew()
    {
        // If an invalid code is given, SpanStatus should be/remain UNSET.
        $status = StatusCode::new();
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
    }

    public function testNoParams()
    {
        // If an invalid code is given, SpanStatus should be/remain UNSET.
        $status = new StatusCode();
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
    }

    public function testGetDescriptionAfterFunctionNew()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */
        $status = StatusCode::new(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        // Invalid should return UNSET.
        $status = StatusCode::new('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
    }

    public function testGetDescription()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */
        $status = new StatusCode('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
    }
    public function testSetStatusWithoutDescription()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */

        $status = StatusCode::new('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::OK);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::UNSET);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::UNSET);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::OK);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());

        // With SpanStatus::new
        $status = StatusCode::new('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::OK);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::UNSET);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::UNSET);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::OK);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
    }

    public function testSetStatusWithDescription()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */

        $status = new StatusCode('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());

        // With SpanStatus::new

        $status = StatusCode::new('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status =StatusCode::new(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
    }

    public function testSetStatusWithoutDescriptionWithInvalidCode()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */

        $status = new StatusCode('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::OK);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::UNSET);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        // With SpanStatus::new

        $status = StatusCode::new('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::OK);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::UNSET);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::ERROR);
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::ERROR], $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
    }

    public function testSetStatusWithDescriptionWithInvalidCode()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */

        $status = new StatusCode(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = new StatusCode(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        // With SpanStatus::new
        $status = StatusCode::new(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status =StatusCode::new(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::OK], $status->getStatusDescription());
        self::assertEquals(StatusCode::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());

        $status = StatusCode::new(StatusCode::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(StatusCode::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(StatusCode::DESCRIPTION[StatusCode::UNSET], $status->getStatusDescription());
        self::assertEquals(StatusCode::UNSET, $status->getCanonicalStatusCode());
    }

    public function testIsOKReturnsTrueForOkStatus()
    {
        $status = StatusCode::new(StatusCode::OK);
        $this->assertTrue($status->isStatusOK());

        $status = new StatusCode(StatusCode::OK);
        $this->assertTrue($status->isStatusOK());

        $status = StatusCode::new(StatusCode::OK, 'description');
        $this->assertTrue($status->isStatusOK());

        $status = new StatusCode(StatusCode::OK, 'description');
        $this->assertTrue($status->isStatusOK());
    }

    public function testIsOKReturnsFalseForNonOkStatus()
    {
        $status = StatusCode::new();
        $this->assertFalse($status->isStatusOK());

        $status = new StatusCode(StatusCode::UNSET);
        $this->assertFalse($status->isStatusOK());

        $status = StatusCode::new(StatusCode::ERROR, 'description');
        $this->assertFalse($status->isStatusOK());

        $status = new StatusCode(StatusCode::UNSET, 'description');
        $this->assertFalse($status->isStatusOK());
    }

    public function testOkReturnsOkStatus(): void
    {
        $okStatus = StatusCode::ok();
        $this->assertTrue($okStatus->isStatusOK());
    }

    public function testNewReturnsSameStatuses(): void
    {
        $status = StatusCode::new(StatusCode::OK);
        $status2 = StatusCode::new(StatusCode::OK);
        $this->assertSame($status->getCanonicalStatusCode(), $status2->getCanonicalStatusCode());
        $this->assertSame($status->getStatusDescription(), $status2->getStatusDescription());
    }

    public function testNewDoesNotCacheDifferentDescriptions(): void
    {
        $status = StatusCode::new(StatusCode::ERROR);
        $status2 = StatusCode::new(StatusCode::ERROR, 'Okay!');
        $this->assertNotSame($status, $status2);
    }
}
