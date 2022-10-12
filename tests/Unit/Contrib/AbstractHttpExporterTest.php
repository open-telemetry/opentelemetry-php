<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib;

use InvalidArgumentException;
use Nyholm\Psr7\Response;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter\AbstractExporterTest;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;

abstract class AbstractHttpExporterTest extends AbstractExporterTest
{
    use UsesHttpClientTrait;

    protected const EXPORTER_DSN = 'https://localhost:1234/foo';

    /**
     * Must be implemented by concrete TestCases
     *
     * @param string $dsn
     * @return SpanExporterInterface
     */
    abstract public function createExporterWithDsn(string $dsn): SpanExporterInterface;

    /**
     * Must be implemented by concrete TestCases
     *
     * @return string
     */
    abstract public function getExporterClass(): string;

    public function createExporter(): SpanExporterInterface
    {
        return $this->createExporterWithDsn(static::EXPORTER_DSN);
    }

    /**
     * @dataProvider exporterResponseStatusDataProvider
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function test_exporter_response_status($responseStatus, $expected): void
    {
        $this->getClientInterfaceMock()->method('sendRequest')
            ->willReturn(new Response($responseStatus));

        $this->assertEquals(
            $expected,
            $this->createExporter()->export([
                $this->createMock(SpanData::class),
            ])->await(),
        );
    }

    public function exporterResponseStatusDataProvider(): array
    {
        return [
            'ok'                => [200, true],
            'not found'         => [404, false],
            'not authorized'    => [401, false],
            'bad request'       => [402, false],
            'too many requests' => [429, false],
            'server error'      => [500, false],
            'timeout'           => [503, false],
            'bad gateway'       => [502, false],
        ];
    }

    /**
     * @dataProvider invalidDsnDataProvider
     */
    public function test_throws_exception_if_invalid_dsn_is_passed($invalidDsn): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createExporterWithDsn($invalidDsn);
    }

    public function invalidDsnDataProvider(): array
    {
        return [
            'missing scheme' => ['host:123/path'],
            'missing host' => ['scheme://'],
            'invalid port' => ['scheme://host:port/path'],
            'invalid scheme' => ['1234://host:port/path'],
            'invalid host' => ['scheme:///end:1234/path'],
            'unimplemented path' => ['scheme:///host:1234/api/v1/spans'],
        ];
    }

    /**
     * @dataProvider clientExceptionDataProvider
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function test_client_exception_decides_return_code($exception, $expected): void
    {
        $client = $this->getClientInterfaceMock();
        $client->method('sendRequest')
            ->willThrowException($exception);

        $this->assertEquals(
            $expected,
            $this->createExporter()->export([
                $this->createMock(SpanData::class),
            ])->await(),
        );
    }

    public function clientExceptionDataProvider(): array
    {
        return [
            'client'    => [
                $this->createMock(ClientExceptionInterface::class),
                false,
            ],
            'network'   => [
                $this->createMock(NetworkExceptionInterface::class),
                false,
            ],
            'request'   => [
                $this->createMock(RequestExceptionInterface::class),
                false,
            ],
        ];
    }

    public function test_from_connection_string(): void
    {
        $exporterClass = static::getExporterClass();

        $this->assertNotSame(
            call_user_func([$exporterClass, 'fromConnectionString'], self::EXPORTER_DSN, $exporterClass, 'foo'),
            call_user_func([$exporterClass, 'fromConnectionString'], self::EXPORTER_DSN, $exporterClass, 'foo')
        );
    }
}
