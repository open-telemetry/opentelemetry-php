<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Trace\Propagation;

use OpenTelemetry\API\Trace\Propagation\TraceContextValidator;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\API\Trace\Propagation\TraceContextValidator::class)]
class TraceContextValidatorTest extends TestCase
{
    private const INVALID_TRACE_FLAG = 'f';
    private const TRACE_FLAG = '11';
    private const TRACE_VERSION = '00';
    private const INVALID_TRACE_VERSION = 'ff';

    public function test_invalid_trace_flag(): void
    {
        $this->assertFalse(TraceContextValidator::isValidTraceFlag(self::INVALID_TRACE_FLAG));
    }

    public function test_valid_trace_flag(): void
    {
        $this->assertTrue(TraceContextValidator::isValidTraceFlag(self::TRACE_FLAG));
    }

    public function test_valid_trace_version(): void
    {
        $this->assertTrue(TraceContextValidator::isValidTraceVersion(self::TRACE_VERSION));
    }

    public function test_invalid_trace_version(): void
    {
        $this->assertFalse(TraceContextValidator::isValidTraceVersion(self::INVALID_TRACE_VERSION));
    }
}
