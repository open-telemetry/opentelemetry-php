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
        yield '0 -> -2^63' => ['0000000000000000', PHP_INT_MIN];
        yield '1 -> -2^63 + 1' => ['0000000000000001', -9223372036854775807];
        yield '2^63 - 1 -> -1' => ['7FFFFFFFFFFFFFFF', -1];
        yield '2^63 -> 0' => ['8000000000000000', 0];
        yield '2^64 - 2 -> 2^63 - 2' => ['FFFFFFFFFFFFFFFE', 9223372036854775806];
        yield '2^64 - 1 -> 2^63 - 1' => ['FFFFFFFFFFFFFFFF', 9223372036854775807];
    }
}
