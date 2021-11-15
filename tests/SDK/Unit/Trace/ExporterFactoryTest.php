<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use Exception;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use OpenTelemetry\Contrib as Path;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use PHPUnit\Framework\TestCase;

class ExporterFactoryTest extends TestCase
{
    public function setUp(): void
    {
        HttpClientDiscovery::prependStrategy(MockClientStrategy::class);
    }

    /**
     * @test
     */
    public function testIfExporterHasCorrectEndpoint()
    {
        $input = 'zipkin+http://zipkin:9411/api/v2/spans';
        $factory = new ExporterFactory('test.zipkin');
        $exporter = $factory->fromConnectionString($input);
        $this->assertInstanceOf(Path\Zipkin\Exporter::class, $exporter);

        $input = 'jaeger+http://jaeger:9412/api/v2/spans';
        $factory = new ExporterFactory('test.jaeger');
        $exporter = $factory->fromConnectionString($input);
        $this->assertInstanceOf(Path\Jaeger\Exporter::class, $exporter);

        $input = 'newrelic+https://trace-api.newrelic.com/trace/v1?licenseKey="23423423';
        $factory = new ExporterFactory('test.newrelic');
        $exporter = $factory->fromConnectionString($input);
        $this->assertInstanceOf(Path\Newrelic\Exporter::class, $exporter);

        $input = 'otlp+http://';
        $factory = new ExporterFactory('test.otlp');
        $exporter = $factory->fromConnectionString($input);
        $this->assertInstanceOf(Path\OtlpHttp\Exporter::class, $exporter);

        $input = 'otlp+grpc://';
        $factory = new ExporterFactory('test.otlpgrpc');
        $exporter = $factory->fromConnectionString($input);
        $this->assertInstanceOf(Path\OtlpGrpc\Exporter::class, $exporter);

        $input = 'zipkintonewrelic+https://trace-api.newrelic.com/trace/v1?licenseKey="23423423';
        $factory = new ExporterFactory('test.zipkintonewrelic');
        $exporter = $factory->fromConnectionString($input);
        $this->assertInstanceOf(Path\ZipkinToNewrelic\Exporter::class, $exporter);
    }

    /**
     * @test
     */
    public function testInvalidInput()
    {
        $this->expectException(Exception::class);
        $input = 'zipkinhttp://zipkin:9411/api/v2/spans';
        $factory = new ExporterFactory('test.zipkin');
        $exporter = $factory->fromConnectionString($input);

        $this->expectException(Exception::class);
        $input = 'zipkin+http://zipkin:9411/api/v2/spans+extraField';
        $factory = new ExporterFactory('test.zipkin');
        $exporter = $factory->fromConnectionString($input);

        $this->expectException(Exception::class);
        $input = 'zapkin+http://zipkin:9411/api/v2/spans';
        $factory = new ExporterFactory('test.zipkin');
        $exporter = $factory->fromConnectionString($input);

        $this->expectException(Exception::class);
        $input = 'otlp';
        $factory = new ExporterFactory('test.otlp');
        $exporter = $factory->fromConnectionString($input);
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
}
