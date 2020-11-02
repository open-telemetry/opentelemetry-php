<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;


use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use OpenTelemetry\Contrib\otlp\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;

class OtlpExporterTest extends TestCase
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

        $exporter = new Exporter('test.otlp');

        $this->assertEquals(
            $expected,
            $exporter->export([new Span('test.otlp.span', SpanContext::generate())])
        );
    }

    public function exporterResponseStatusesDataProvider()
    {
        return [
            'server error'      => [500, Exporter::FAILED_RETRYABLE],
            'timeout'           => [408, Exporter::FAILED_RETRYABLE],
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

        $exporter = new Exporter('test.otlp');

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
            'request'   => [
                self::createMock(RequestExceptionInterface::class),
                Exporter::FAILED_NOT_RETRYABLE,
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
            (new Exporter('test.otlp'))->export([])
        );
    }

    /**
     * @test
     * @dataProvider invalidDsnDataProvider
     *
     * @param $invalidDsn
     */
    public function shouldThrowExceptionIfInvalidDsnIsPassed($invalidDsn)
    {
        $this->expectException(InvalidArgumentException::class);

        new Exporter('test.otlp', $invalidDsn);
    }

}
