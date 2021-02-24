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

    public function testNoParamsToFunctionNew()
    {
        // If an invalid code is given, SpanStatus should be/remain UNSET.
        $status = SpanStatus::new();
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
    }

    public function testNoParams()
    {
        // If an invalid code is given, SpanStatus should be/remain UNSET.
        $status = new SpanStatus();
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
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

        // Invalid should return UNSET.
        $status = SpanStatus::new('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
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

        $status = SpanStatus::new('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::OK);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::OK);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());

        // With SpanStatus::new
        $status = SpanStatus::new('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::OK);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::ERROR);
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

        $status = new SpanStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

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

        // With SpanStatus::new

        $status = SpanStatus::new('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status =SpanStatus::new(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
    }

    public function testSetStatusWithoutDescriptionWithInvalidCode()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */

        $status = new SpanStatus('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::OK);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        // With SpanStatus::new

        $status = SpanStatus::new('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::OK);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::UNSET);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::ERROR);
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
    }

    public function testSetStatusWithDescriptionWithInvalidCode()
    {
        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */

        $status = new SpanStatus(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = new SpanStatus(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        // With SpanStatus::new
        $status = SpanStatus::new(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status =SpanStatus::new(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::OK, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $status = SpanStatus::new(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
        $status->setStatus('mycode', 'Neunundneunzig Luftballons');
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
    }

    public function testIsOKReturnsTrueForOkStatus()
    {
        $status = SpanStatus::new(SpanStatus::OK);
        $this->assertTrue($status->isStatusOK());

        $status = new SpanStatus(SpanStatus::OK);
        $this->assertTrue($status->isStatusOK());

        $status = SpanStatus::new(SpanStatus::OK, 'description');
        $this->assertTrue($status->isStatusOK());

        $status = new SpanStatus(SpanStatus::OK, 'description');
        $this->assertTrue($status->isStatusOK());
    }

    public function testIsOKReturnsFalseForNonOkStatus()
    {
        $status = SpanStatus::new();
        $this->assertFalse($status->isStatusOK());

        $status = new SpanStatus(SpanStatus::UNSET);
        $this->assertFalse($status->isStatusOK());

        $status = SpanStatus::new(SpanStatus::ERROR, 'description');
        $this->assertFalse($status->isStatusOK());

        $status = new SpanStatus(SpanStatus::UNSET, 'description');
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
