<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\OtlpGrpc\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace;
use PHPUnit\Framework\TestCase;

class OTLPGrpcExporterTest extends TestCase
{

    /**
     * @test
     */
    public function testExporter()
    {
        $exporter = new Exporter();

        $this->assertInstanceOf(Trace\Exporter::class, $exporter);

    }


    /**
     * @test
     */
    public function shouldBeOkToExporterEmptySpansCollection()
    {
        $this->assertEquals(
            Exporter::SUCCESS,
            (new Exporter('test.otlp'))->export([])
        );
    }
    /**
     * @test
     */
    public function failsIfNotRunning()
    {
        $exporter = new Exporter('test.otlp');
        $span = $this->createMock(Span::class);
        $exporter->shutdown();

        $this->assertEquals(\OpenTelemetry\Sdk\Trace\Exporter::FAILED_NOT_RETRYABLE, $exporter->export([$span]));
    }

    public function testHeadersShouldBeConvertedToArray()
    {
        $expected = ['x-aaa' => ['foo'], 'x-bbb' => ['bar']];

        $headers_as_string = (new Exporter)->metadataFromHeaders('x-aaa=foo,x-bbb=bar');

        $this->assertEquals($expected, $headers_as_string);

    }
}
