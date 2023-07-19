<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\OtlpUtil
 */
class OtlpUtilTest extends TestCase
{
    public function test_get_user_agent_header(): void
    {
        $header = OtlpUtil::getUserAgentHeader();
        $this->assertArrayHasKey('User-Agent', $header);
        $this->assertNotNull($header['User-Agent']);
    }

    public function test_method_not_defined(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        OtlpUtil::method('foo');
    }

    /**
     * @dataProvider methodProvider
     */
    public function test_method(string $signal, string $expected): void
    {
        $method = OtlpUtil::method($signal);
        $this->assertStringContainsString($expected, $method);
    }

    public static function methodProvider(): array
    {
        return [
            [Signals::TRACE, 'TraceService'],
            [Signals::METRICS, 'MetricsService'],
            [Signals::LOGS, 'LogsService'],
        ];
    }
}
