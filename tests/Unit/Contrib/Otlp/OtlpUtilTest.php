<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\OtlpUtil
 */
class OtlpUtilTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

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

    /**
     * @dataProvider headersProvider
     */
    public function test_get_headers(string $signal, array $env, array $expected): void
    {
        foreach ($env as $var => $value) {
            $this->setEnvironmentVariable($var, $value);
        }
        $headers = OtlpUtil::getHeaders($signal);

        $this->assertGreaterThanOrEqual(count($expected), $headers);
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $headers);
            $this->assertSame($value, $headers[$key]);
        }
    }

    public static function headersProvider(): array
    {
        return [
            'trace' => [
                'signal' => Signals::TRACE,
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_TRACES_HEADERS => 'foo=bar,baz=bat',
                ],
                'expected' => [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ],
            ],
            'trace with default' => [
                'signal' => Signals::TRACE,
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_HEADERS => 'foo=bar,baz=bat',
                ],
                'expected' => [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ],
            ],
            'metrics' => [
                'signal' => Signals::METRICS,
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_METRICS_HEADERS => 'foo=bar,baz=bat',
                ],
                'expected' => [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ],
            ],
            'metrics with default' => [
                'signal' => Signals::METRICS,
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_HEADERS => 'foo=bar,baz=bat',
                ],
                'expected' => [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ],
            ],
            'logs' => [
                'signal' => Signals::LOGS,
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_LOGS_HEADERS => 'foo=bar,baz=bat',
                ],
                'expected' => [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ],
            ],
            'logs with default' => [
                'signal' => Signals::LOGS,
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_HEADERS => 'foo=bar,baz=bat',
                ],
                'expected' => [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ],
            ],
            'url-encoded values' => [
                'signal' => Signals::TRACE,
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_HEADERS => 'Authorization=Bearer%20secret,foo=%21%40%23%24%25%5E%26%2A%28%29',
                ],
                'expected' => [
                    'Authorization' => 'Bearer secret',
                    'foo' => '!@#$%^&*()',
                ],
            ],
        ];
    }
}
