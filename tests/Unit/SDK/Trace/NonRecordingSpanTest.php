<?php

declare(strict_types=1);
final 
namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NonRecordingSpan::class)]
class NonRecordingSpanTest extends TestCase
{
    public function test_is_not_recording(): void
    {
        $this->assertFalse(NonRecordingSpan::getInvalid()->isRecording());
    }

    public function test_has_invalid_context_and_default_span_options(): void
    {
        $context = NonRecordingSpan::getInvalid()->getContext();
        $this->assertSame(API\TraceFlags::DEFAULT, $context->getTraceFlags());
        $this->assertNull($context->getTraceState());
    }
}
