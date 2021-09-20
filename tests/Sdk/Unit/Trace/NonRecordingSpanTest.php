<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\NonRecordingSpan;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class NonRecordingSpanTest extends TestCase
{
    public function testIsNotRecording(): void
    {
        $this->assertFalse(NonRecordingSpan::getInvalid()->isRecording());
    }

    public function testHasInvalidContextAndDefaultSpanOptions(): void
    {
        $context = NonRecordingSpan::getInvalid()->getContext();
        $this->assertSame(API\SpanContext::TRACE_FLAG_DEFAULT, $context->getTraceFlags());
        $this->assertNull($context->getTraceState());
    }
}
