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
    private const INVALID_TRACE_Flag = 'f';
    private const TRACE_Flag = '11';
    private const Trace_Version = '00';

    public function test_is__invalid__trace__flag(): void
    {
        $this->assertFalse(TraceContextValidator::isValidTraceFlag(self::INVALID_TRACE_Flag));
    }

    public function test_is__valid__trace__flag(): void
    {
        $this->assertTrue(TraceContextValidator::isValidTraceFlag(self::TRACE_Flag));
    }

    public function test_is__invalid__trace__version(): void
    {
        $this->assertTrue(TraceContextValidator::isValidTraceVersion(self::Trace_Version));
    }
}
