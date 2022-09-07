<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\SpanContextValidator
 */
class SpanContextValidatorTest extends TestCase
{
    private const TRACE_ID = 'ff000000000000000000000000000041';
    private const SPAN_ID = 'ff00000000000041';

    public function test_is__invalid__trace__id(): void
    {
        $this->assertFalse(SpanContextValidator::isValidTraceID(SpanContext::INVALID_TRACE));
    }

    public function test_is__valid__trace__id(): void
    {
        $this->assertTrue(SpanContextValidator::isValidTraceID(self::TRACE_ID));
    }

    public function test_is__invalid__span__id(): void
    {
        $this->assertFalse(SpanContextValidator::isValidSpanID(SpanContext::INVALID_SPAN));
    }

    public function test_is__valid__span__id(): void
    {
        $this->assertTrue(SpanContextValidator::isValidSpanID(self::SPAN_ID));
    }
}
