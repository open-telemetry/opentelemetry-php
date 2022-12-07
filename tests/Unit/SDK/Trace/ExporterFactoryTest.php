<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use OpenTelemetry\Contrib;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Trace\ExporterFactory
 */
class ExporterFactoryTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function setUp(): void
    {
        HttpClientDiscovery::prependStrategy(MockClientStrategy::class);
    }

    /**
     * @group trace-compliance
     */
    public function test_accepts_none_exporter_env_var(): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', 'none');
        $factory = new ExporterFactory();
        $this->assertNull($factory->create());
    }

    /**
     * @dataProvider envProvider
     * @psalm-param class-string $expected
     * @group trace-compliance
     */
    public function test_create_from_environment(string $exporter, array $env, string $expected): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', $exporter);
        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new ExporterFactory();
        $this->assertInstanceOf($expected, $factory->create());
    }

    public function envProvider(): array
    {
        return [
            'otlp+http/protobuf from traces protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_TRACES_PROTOCOL' => 'http/protobuf'],
                Contrib\Otlp\SpanExporter::class,
            ],
            'otlp+http/protobuf from protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'http/protobuf'],
                Contrib\Otlp\SpanExporter::class,
            ],
            'otlp+grpc from traces protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_TRACES_PROTOCOL' => 'grpc'],
                Contrib\Otlp\SpanExporter::class,
            ],
            'otlp+grpc from protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'grpc'],
                Contrib\Otlp\SpanExporter::class,
            ],
            'otlp+json from protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'http/json'],
                Contrib\Otlp\SpanExporter::class,
            ],
            'console' => [
                'console', [], ConsoleSpanExporter::class,
            ],
        ];
    }

    /**
     * @dataProvider invalidEnvProvider
     * @group trace-compliance
     */
    public function test_throws_exception_for_invalid_or_unsupported_exporter_configs(string $exporter, array $env = []): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', $exporter);
        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new ExporterFactory();
        $this->expectException(Exception::class);
        $factory->create();
    }

    public function invalidEnvProvider(): array
    {
        return [
            'newrelic' => ['newrelic'],
            'zipkintonewrelic' => ['zipkintonewrelic'],
            'otlp+invalid protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'foo'],
            ],
            'unknown exporter' => ['foo'],
            'multiple exporters' => ['newrelic,zipkin'],
        ];
    }
}
