<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Contrib as Path;
use OpenTelemetry\Sdk\Trace\ExporterFactory as ExporterFactory;
use PHPUnit\Framework\TestCase;

class ExporterFactoryTest extends TestCase
{
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

        $input = 'otlp+';
        $factory = new ExporterFactory('test.otlp');
        $exporter = $factory->fromConnectionString($input);
        $this->assertInstanceOf(Path\Otlp\Exporter::class, $exporter);

        $input = 'otlpgrpc+';
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
        $input = 'zipkinhttp://zipkin:9411/api/v2/spans';
        $factory = new ExporterFactory('test.zipkin');
        $exporter = $factory->fromConnectionString($input);
        $this->assertNull($exporter);

        $input = 'zipkin+http://zipkin:9411/api/v2/spans+extraField';
        $factory = new ExporterFactory('test.zipkin');
        $exporter = $factory->fromConnectionString($input);
        $this->assertNull($exporter);

        $input = 'zapkin+http://zipkin:9411/api/v2/spans';
        $factory = new ExporterFactory('test.zipkin');
        $exporter = $factory->fromConnectionString($input);
        $this->assertNull($exporter);

        $input = 'otlp';
        $factory = new ExporterFactory('test.otlp');
        $exporter = $factory->fromConnectionString($input);
        $this->assertNull($exporter);
    }

    /**
     * @test
     */
    public function testMissingLicenseKey()
    {
        $input = 'newrelic+https://trace-api.newrelic.com/trace/v1';
        $factory = new ExporterFactory('test.newrelic');
        $exporter = $factory->fromConnectionString($input);
        $this->assertNull($exporter);

        $input = 'zipkintonewrelic+https://trace-api.newrelic.com/trace/v1';
        $factory = new ExporterFactory('test.zipkintonewrelic');
        $exporter = $factory->fromConnectionString($input);
        $this->assertNull($exporter);
    }
}
