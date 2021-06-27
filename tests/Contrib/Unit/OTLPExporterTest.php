<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use OpenTelemetry\Contrib\Otlp\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;

class OTLPExporterTest extends TestCase
{

    /**
     * @test
     * @dataProvider exporterResponseStatusesDataProvider
     */
    public function exporterResponseStatuses($responseStatus, $expected)
    {
        $client = self::createMock(ClientInterface::class);
        $client->method('sendRequest')->willReturn(
            new Response($responseStatus)
        );

        $exporter = new Exporter('test.otlp', $client, new HttpFactory(), new HttpFactory());

        $this->assertEquals(
            $expected,
            $exporter->export([new Span('test.otlp.span', SpanContext::generate())])
        );
    }

    public function exporterResponseStatusesDataProvider()
    {
        return [
            'ok'                => [200, Exporter::SUCCESS],
            'not found'         => [404, Exporter::FAILED_NOT_RETRYABLE],
            'not authorized'    => [401, Exporter::FAILED_NOT_RETRYABLE],
            'bad request'       => [402, Exporter::FAILED_NOT_RETRYABLE],
            'too many requests' => [429, Exporter::FAILED_NOT_RETRYABLE],
            'server error'      => [500, Exporter::FAILED_RETRYABLE],
            'timeout'           => [503, Exporter::FAILED_RETRYABLE],
            'bad gateway'       => [502, Exporter::FAILED_RETRYABLE],
        ];
    }

    /**
     * @test
     * @dataProvider clientExceptionsShouldDecideReturnCodeDataProvider
     */
    public function clientExceptionsShouldDecideReturnCode($exception, $expected)
    {
        $client = self::createMock(ClientInterface::class);
        $client->method('sendRequest')->willThrowException($exception);

        $exporter = new Exporter('test.otlp', $client, new HttpFactory(), new HttpFactory());

        $this->assertEquals(
            $expected,
            $exporter->export([new Span('test.otlp.span', SpanContext::generate())])
        );
    }

    public function clientExceptionsShouldDecideReturnCodeDataProvider()
    {
        return [
            'client'    => [
                self::createMock(ClientExceptionInterface::class),
                Exporter::FAILED_RETRYABLE,
            ],
            'network'   => [
                self::createMock(NetworkExceptionInterface::class),
                Exporter::FAILED_RETRYABLE,
            ],
        ];
    }

    /**
     * @test
     */
    public function shouldBeOkToExporterEmptySpansCollection()
    {
        $this->assertEquals(
            Exporter::SUCCESS,
            (new Exporter('test.otlp', new Client(), new HttpFactory(), new HttpFactory()))->export([])
        );
    }
    /**
     * @test
     */
    public function failsIfNotRunning()
    {
        $exporter = new Exporter('test.otlp', new Client(), new HttpFactory(), new HttpFactory());
        $span = $this->createMock(Span::class);
        $exporter->shutdown();

        $this->assertEquals(\OpenTelemetry\Sdk\Trace\Exporter::FAILED_NOT_RETRYABLE, $exporter->export([$span]));
    }
}
