<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use OpenTelemetry\Contrib as Path;
use OpenTelemetry\SDK\ConfigBuilder;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use PHPUnit\Framework\TestCase;

class ExporterFactoryTest extends TestCase
{
    use EnvironmentVariables;

    private ConfigBuilder $configBuilder;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function setUp(): void
    {
        HttpClientDiscovery::prependStrategy(MockClientStrategy::class);
        $this->configBuilder = new ConfigBuilder();
    }

    /**
     * @test
     * @dataProvider endpointProvider
     */
    public function exporterFactory_exporterHasCorrectEndpoint($name, $input, $expectedClass)
    {
        $factory = new ExporterFactory($name);
        $exporter = $factory->fromConnectionString($input);
        $this->assertInstanceOf($expectedClass, $exporter);
    }

    public function endpointProvider()
    {
        return [
            'zipkin' => ['test.zipkin', 'zipkin+http://zipkin:9411/api/v2/spans', Path\Zipkin\Exporter::class],
            'jaeger' => ['test.jaeger', 'jaeger+http://jaeger:9412/api/v2/spans', Path\Jaeger\Exporter::class],
            'newrelic' => ['rest.newrelic', 'newrelic+https://trace-api.newrelic.com/trace/v1?licenseKey="23423423', Path\Newrelic\Exporter::class],
            'otlp+http' => ['test.otlp', 'otlp+http://', Path\OtlpHttp\Exporter::class],
            'otlp+grpc' => ['test.otlpgrpc', 'otlp+grpc://', Path\OtlpGrpc\Exporter::class],
            'zipkintonewrelic' => ['test.zipkintonewrelic', 'zipkintonewrelic+https://trace-api.newrelic.com/trace/v1?licenseKey="23423423', Path\ZipkinToNewrelic\Exporter::class],
            'console' => ['test.console', 'console+php://stdout', ConsoleSpanExporter::class],
        ];
    }

    /**
     * @test
     * @dataProvider invalidConnectionStringProvider
     */
    public function exporterFactory_invalidConnectionString(string $name, string $input)
    {
        $this->expectException(Exception::class);
        $factory = new ExporterFactory($name);
        $factory->fromConnectionString($input);
    }

    public function invalidConnectionStringProvider()
    {
        return [
            'zipkin without +' => ['test.zipkin', 'zipkinhttp://zipkin:9411/api/v2/spans'],
            'zipkin with extra field' => ['test.zipkin', 'zipkin+http://zipkin:9411/api/v2/spans+extraField'],
            'zapkin' => ['zipkin.test', 'zapkin+http://zipkin:9411/api/v2/spans'],
            'otlp' => ['test.otlp', 'otlp'],
        ];
    }

    /**
     * @test
     */
    public function testMissingLicenseKey()
    {
        $this->expectException(Exception::class);
        $input = 'newrelic+https://trace-api.newrelic.com/trace/v1';
        $factory = new ExporterFactory('test.newrelic');
        $exporter = $factory->fromConnectionString($input);

        $this->expectException(Exception::class);
        $input = 'zipkintonewrelic+https://trace-api.newrelic.com/trace/v1';
        $factory = new ExporterFactory('test.zipkintonewrelic');
        $exporter = $factory->fromConnectionString($input);
    }

    /**
     * @test
     */
    public function exporterFactory_acceptsNoneExporter()
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', 'none');
        $factory = new ExporterFactory('test.fromEnv');
        $this->assertNull($factory->fromConfig($this->configBuilder->build()));
    }

    /**
     * @test
     * @dataProvider envProvider
     * @psalm-param class-string $expected
     */
    public function exporterFactory_createFromEnvironment(string $exporter, array $env, string $expected)
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', $exporter);
        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new ExporterFactory('test.fromEnv');
        $this->assertInstanceOf($expected, $factory->fromConfig($this->configBuilder->build()));
    }

    public function envProvider()
    {
        return [
            'otlp+http/protobuf from traces protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_TRACES_PROTOCOL' => 'http/protobuf'],
                Path\OtlpHttp\Exporter::class,
            ],
            'otlp+http/protobuf from protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'http/protobuf'],
                Path\OtlpHttp\Exporter::class,
            ],
            'otlp+grpc from traces protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_TRACES_PROTOCOL' => 'grpc'],
                Path\OtlpGrpc\Exporter::class,
            ],
            'otlp+grpc from protocol' => [
                'otlp',
                ['OTEL_EXPORTER_OTLP_PROTOCOL' => 'grpc'],
                Path\OtlpGrpc\Exporter::class,
            ],
            'console' => [
                'console', [], ConsoleSpanExporter::class,
            ],
            'oltp without protocol' => [
                'otlp',
                [],
                Path\OtlpHttp\Exporter::class,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidEnvProvider
     */
    public function exporterFactory_throwsExceptionForInvalidOrUnsupportedExporterConfigs(string $exporter, array $env = [])
    {
        $this->setEnvironmentVariable('OTEL_TRACES_EXPORTER', $exporter);
        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new ExporterFactory('test');
        $this->expectException(Exception::class);
        $factory->fromConfig($this->configBuilder->build());
    }

    public function invalidEnvProvider()
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
            'unknown exporter' => ['foo'],
            'multiple exporters' => ['jaeger,zipkin'],
        ];
    }
}
