<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Trace\NonRecordingSpan;
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
