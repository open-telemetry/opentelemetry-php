<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanUtils;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\SpanUtils
 */
class SpanUtilsTest extends TestCase
{
    public function test_getter_retrieves_what_setter_set(): void
    {
        //Anything that will implement storeInContext like AbstractSpan's implementation of it should work for this
        $spanToSet = new NonRecordingSpan(SpanContext::getInvalid());

        SpanUtils::setSpanIntoNewContext($spanToSet);

        $currentSpan = SpanUtils::getCurrentSpan();

        $this->assertSame($spanToSet, $currentSpan);
    }
}
