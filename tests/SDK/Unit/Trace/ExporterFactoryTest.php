<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

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
     * @dataProvider endpointProvider
     */
    public function testExporterHasCorrectEndpoint($name, $input, $expectedClass): void
    {
        $factory = new ExporterFactory($name);
        $exporter = $factory->fromConnectionString($input);
        $this->assertInstanceOf($expectedClass, $exporter);
    }

    public function endpointProvider(): array
    {
        return [
            'zipkin' => ['test.zipkin', 'zipkin+http://zipkin:9411/api/v2/spans', Contrib\Zipkin\Exporter::class],
            'jaeger' => ['test.jaeger', 'jaeger+http://jaeger:9412/api/v2/spans', Contrib\Jaeger\Exporter::class],
            'newrelic' => ['rest.newrelic', 'newrelic+https://trace-api.newrelic.com/trace/v1?licenseKey=abc23423423', Contrib\Newrelic\Exporter::class],
            'otlp+http' => ['test.otlp', 'otlp+http', Contrib\OtlpHttp\Exporter::class],
            'otlp+grpc' => ['test.otlpgrpc', 'otlp+grpc://otlp:4317', Contrib\OtlpGrpc\Exporter::class],
            'zipkintonewrelic' => ['test.zipkintonewrelic', 'zipkintonewrelic+https://trace-api.newrelic.com/trace/v1?licenseKey=abc23423423', Contrib\ZipkinToNewrelic\Exporter::class],
            'console' => ['test.console', 'console', ConsoleSpanExporter::class],
        ];
    }

    /**
     * @dataProvider invalidConnectionStringProvider
     */
    public function testInvalidConnectionString(string $name, string $input): void
    {
        $this->expectException(Exception::class);
        $factory = new ExporterFactory($name);
        $factory->fromConnectionString($input);
    }

    public function invalidConnectionStringProvider(): array
    {
        return [
            'zipkin without +' => ['test.zipkin', 'zipkinhttp://zipkin:9411/api/v2/spans'],
            'zapkin' => ['zipkin.test', 'zapkin+http://zipkin:9411/api/v2/spans'],
            'otlp' => ['test.otlp', 'otlp'],
            'test' => ['test', 'test+http://test:1345'],
        ];
    }

    public function testAcceptsNoneExporterEnvVar()
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', 'none');
        $factory = new ExporterFactory('test.fromEnv');
        $this->assertNull($factory->fromEnvironment());
    }

    /**
     * @dataProvider envProvider
     * @psalm-param class-string $expected
     */
    public function testCreateFromEnvironment(string $exporter, array $env, string $expected)
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', $exporter);
        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new ExporterFactory('test.fromEnv');
        $this->assertInstanceOf($expected, $factory->fromEnvironment());
    }

    public function envProvider(): array
    {
        return [
            'otlp+http/protobuf from traces protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_TRACES_PROTOCOL' => 'http/protobuf'],
                Contrib\OtlpHttp\Exporter::class,
            ],
            'otlp+http/protobuf from protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'http/protobuf'],
                Contrib\OtlpHttp\Exporter::class,
            ],
            'otlp+grpc from traces protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_TRACES_PROTOCOL' => 'grpc'],
                Contrib\OtlpGrpc\Exporter::class,
            ],
            'otlp+grpc from protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'grpc'],
                Contrib\OtlpGrpc\Exporter::class,
            ],
            'console' => [
                'console', [], ConsoleSpanExporter::class,
            ],
        ];
    }

    /**
     * @dataProvider invalidEnvProvider
     */
    public function testThrowsExceptionForInvalidOrUnsupportedExporterConfigs(string $exporter, array $env = [])
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', $exporter);
        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new ExporterFactory('test');
        $this->expectException(Exception::class);
        $factory->fromEnvironment();
    }

    public function invalidEnvProvider(): array
    {
        return [
            'jaeger' => ['jaeger'],
            'zipkin' => ['zipkin'],
            'newrelic' => ['newrelic'],
            'zipkintonewrelic' => ['zipkintonewrelic'],
            'otlp+http/json' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'http/json'],
            ],
            'otlp+invalid protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'foo'],
            ],
            'oltp without protocol' => ['otlp'],
            'unknown exporter' => ['foo'],
            'multiple exporters' => ['jaeger,zipkin'],
        ];
    }

    public function testNonExistingExporterEnvVar(): void
    {
        $this->expectException(Exception::class);

        (new ExporterFactory())->fromEnvironment();
    }
}
