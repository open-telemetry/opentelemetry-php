<?php

declare(strict_types=1);final 

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use Exception;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use OpenTelemetry\Contrib;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExporterFactory::class)]
class ExporterFactoryTest extends TestCase
{
    use TestState;

    #[\Override]
    public function setUp(): void
    {
        Psr18ClientDiscovery::prependStrategy(MockClientStrategy::class);

        // Ensure all required factories are registered in the Registry
        $this->ensureRequiredFactoriesRegistered();
    }

    private function ensureRequiredFactoriesRegistered(): void
    {
        // Register OTLP factories if not already registered
        try {
            Registry::spanExporterFactory('otlp');
        } catch (\RuntimeException $e) {
            Registry::registerSpanExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\SpanExporterFactory::class);
        }

        // Register transport factories if not already registered
        try {
            Registry::transportFactory('http');
        } catch (\RuntimeException $e) {
            Registry::registerTransportFactory('http', \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory::class);
        }

        try {
            Registry::transportFactory('grpc');
        } catch (\RuntimeException $e) {
            Registry::registerTransportFactory('grpc', \OpenTelemetry\Contrib\Grpc\GrpcTransportFactory::class);
        }
    }

    #[Group('trace-compliance')]
    public function test_accepts_none_exporter_env_var(): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', 'none');
        $factory = new ExporterFactory();
        $this->assertNull($factory->create());
    }

    /**
     * @psalm-param class-string $expected
     */
    #[DataProvider('envProvider')]
    #[Group('trace-compliance')]
    public function test_create_from_environment(string $exporter, array $env, string $expected): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', $exporter);
        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new ExporterFactory();
        $this->assertInstanceOf($expected, $factory->create());
    }

    public static function envProvider(): array
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

    #[DataProvider('invalidEnvProvider')]
    #[Group('trace-compliance')]
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

    public static function invalidEnvProvider(): array
    {
        return [
            'otlp+invalid protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'foo'],
            ],
            'unknown exporter' => ['foo'],
            'multiple exporters' => ['console,zipkin'],
        ];
    }
}
