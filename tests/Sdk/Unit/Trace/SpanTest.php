<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use Exception;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class SpanTest extends TestCase
{
    public function testRecordExceptionEventTimestamp(): void
    {
        $span = new Span('span', new SpanContext(
            'faa0c74e14bd78114ec2bc447ad94ec9',
            '50a75f197c3de59a',
            API\SpanContext::TRACE_FLAG_SAMPLED
        ));

        $span->recordException(new Exception('err'));

        // The timestamp of the event should be greater than the start time of the span.
        $this->assertGreaterThan(
            $span->getStart(),
            $span->getEvents()->getIterator()->current()->getTimestamp()
        );
    }
}
