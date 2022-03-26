<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Jaeger\IdConverter;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\IdConverter
 */
class IdConverterTest extends TestCase
{
    /**
     * @dataProvider edgeCaseData
     */
    public function test_correctly_converts_edge_cases($spanId, $convertedSpanId)
    {
        $this->assertSame($convertedSpanId, IdConverter::convertOtelToJaegerSpanId($spanId));
    }

    public function edgeCaseData(): iterable 
    {
        yield ['0000000000000000', PHP_INT_MIN]; //Output should be -2^63 //For some reason hardcoding -9223372036854775808 (-2^63) instead of PHP_INT_MIN isn't liked by PHPUnit. Might be a high precision arithmetic issue?
        yield ['0000000000000001', -9223372036854775807]; //Output is -2^63 + 1
        yield ['7FFFFFFFFFFFFFFF', -1]; //Input is 2^63 - 1
        yield ['8000000000000000', 0]; //Input is 2^63
        yield ['FFFFFFFFFFFFFFFE', 9223372036854775806]; //Input is 2^64 - 2, Output is 2^63 - 2
        yield ['FFFFFFFFFFFFFFFF', 9223372036854775807]; //Input is 2^64 - 1, Output is 2^63 - 1
    }
}
