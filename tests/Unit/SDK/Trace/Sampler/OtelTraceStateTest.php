<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\Sampler;

use OpenTelemetry\SDK\Trace\Sampler\OtelTraceState;
use PHPUnit\Framework\TestCase;

final class OtelTraceStateTest extends TestCase
{
    private static function getXString(int $len): string
    {
        return str_repeat('X', $len);
    }

    public function test(): void
    {
        $this->assertSame('', OtelTraceState::parse('')->serialize());
        $this->assertSame('', OtelTraceState::parse('')->serialize());

        $this->assertSame('rv:1234567890abcd', OtelTraceState::parse('rv:1234567890abcd')->serialize());
        $this->assertSame('rv:01020304050607', OtelTraceState::parse('rv:01020304050607')->serialize());
        $this->assertSame('', OtelTraceState::parse('rv:1234567890abcde')->serialize());

        $this->assertSame('th:1234567890abcd', OtelTraceState::parse('th:1234567890abcd')->serialize());
        $this->assertSame('th:01020304050607', OtelTraceState::parse('th:01020304050607')->serialize());
        $this->assertSame('th:1', OtelTraceState::parse('th:10000000000000')->serialize());
        $this->assertSame('th:12345', OtelTraceState::parse('th:1234500000000')->serialize());
        $this->assertSame('th:0', OtelTraceState::parse('th:0')->serialize());
        $this->assertSame('', OtelTraceState::parse('th:100000000000000')->serialize());
        $this->assertSame('', OtelTraceState::parse('th:1234567890abcde')->serialize());

        $this->assertSame(
            'th:1234567890abcd;rv:1234567890abcd;a:' . self::getXString(214) . ';x:3',
            OtelTraceState::parse('a:' . self::getXString(214) . ';rv:1234567890abcd;th:1234567890abcd;x:3')
                          ->serialize()
        );
        $this->assertSame(
            '',
            OtelTraceState::parse('a:' . self::getXString(215) . ';rv:1234567890abcd;th:1234567890abcd;x:3')
                          ->serialize()
        );

        $this->assertSame('', OtelTraceState::parse('th:x')->serialize());
        $this->assertSame('', OtelTraceState::parse('th:100000000000000')->serialize());
        $this->assertSame('th:1', OtelTraceState::parse('th:10000000000000')->serialize());
        $this->assertSame('th:1', OtelTraceState::parse('th:1000000000000')->serialize());
        $this->assertSame('th:1', OtelTraceState::parse('th:100000000000')->serialize());
    }
}
