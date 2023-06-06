<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\NonRecordingSpan
 */
class NonRecordingSpanTest extends TestCase
{
    public function test_is_not_recording(): void
    {
        $this->assertFalse(API\NonRecordingSpan::getInvalid()->isRecording());
    }

    public function test_has_invalid_context_and_default_span_options(): void
    {
        $context = API\NonRecordingSpan::getInvalid()->getContext();
        $this->assertSame(API\TraceFlags::DEFAULT, $context->getTraceFlags());
        $this->assertNull($context->getTraceState());
    }
}
