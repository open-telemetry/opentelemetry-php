<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace;

use OpenTelemetry\API\Trace\Propagation\TraceContextValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\TraceContextValidator
 */
class TraceContextValidatorTest extends TestCase
{
    private const INVALID_TRACE_FLAG = 'f';
    private const TRACE_FLAG = '11';
    private const TRACE_VERSION = '00';

    public function test_is_invalid_trace_flag(): void
    {
        $this->assertFalse(TraceContextValidator::isValidTraceFlag(self::INVALID_TRACE_FLAG));
    }

    public function test_is_valid_trace_flag(): void
    {
        $this->assertTrue(TraceContextValidator::isValidTraceFlag(self::TRACE_FLAG));
    }

    public function test_is_invalid_trace_version(): void
    {
        $this->assertTrue(TraceContextValidator::isValidTraceVersion(self::TRACE_VERSION));
    }
}
