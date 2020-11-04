<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use OpenTelemetry\Contrib\Zipkin\Exporter;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;

class ZipkinExporterTest extends TestCase
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

        $exporter = new Exporter('test.zipkin', 'scheme://host:123/path', null, $client);

        $this->assertEquals(
            $expected,
            $exporter->export([new Span('test.zipkin.span', SpanContext::generate())])
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

        $exporter = new Exporter('test.zipkin', 'scheme://host:123/path', null, $client);

        $this->assertEquals(
            $expected,
            $exporter->export([new Span('test.zipkin.span', SpanContext::generate())])
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
            (new Exporter('test.zipkin', 'scheme://host:123/path'))->export([])
        );
    }

    /**
     * @test
     * @dataProvider invalidDsnDataProvider
     */
    public function shouldThrowExceptionIfInvalidDsnIsPassed(string $invalidDsn)
    {
        $this->expectException(InvalidArgumentException::class);

        new Exporter('test.zipkin', $invalidDsn);
    }

    public function invalidDsnDataProvider()
    {
        return [
            'missing scheme' => ['host:123/path'],
            'missing host' => ['scheme://123/path'],
            'missing port' => ['scheme://host/path'],
            'missing path' => ['scheme://host:123'],
            'invalid port' => ['scheme://host:port/path'],
            'invalid scheme' => ['1234://host:port/path'],
            'invalid host' => ['scheme:///end:1234/path'],
        ];
    }

    /**
     * @test
     */
    public function failsIfNotRunning()
    {
        $exporter = new Exporter('test.jaeger', 'scheme://host:123/path');
        $span = $this->createMock(Span::class);
        $exporter->shutdown();

        $this->assertEquals($exporter->export([$span]), \OpenTelemetry\Sdk\Trace\Exporter::FAILED_NOT_RETRYABLE);
    }
}
