<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\OtlpHttp;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use OpenTelemetry\Contrib\OtlpHttp\Exporter;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\Unit\Contrib\UsesHttpClientTrait;
use OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter\AbstractExporterTest;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;

/**
 * @covers \OpenTelemetry\Contrib\OtlpHttp\Exporter
 */
class OTLPHttpExporterTest extends AbstractExporterTest
{
    use EnvironmentVariables;
    use UsesHttpClientTrait;

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function createExporter(): SpanExporterInterface
    {
        return new Exporter(Exporter::createTransport(new PsrTransportFactory(
            $this->getClientInterfaceMock(),
            $this->getRequestFactoryInterfaceMock(),
            $this->getStreamFactoryInterfaceMock(),
        )));
    }

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @dataProvider exporterResponseStatusDataProvider
     */
    public function test_exporter_response_status($responseStatus, $expected): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willReturn(
            new Response($responseStatus, [])
        );
        /** @var ClientInterface $client */
        $exporter = new Exporter(Exporter::createTransport(new PsrTransportFactory($client, new HttpFactory(), new HttpFactory())));

        $this->assertEquals(
            min($expected, 1),
            $exporter->export([new SpanData()])->await(),
        );
    }

    public function exporterResponseStatusDataProvider(): array
    {
        return [
            'ok'                => [200, SpanExporterInterface::STATUS_SUCCESS],
            'not found'         => [404, SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE],
            'not authorized'    => [401, SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE],
            'bad request'       => [402, SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE],
            'too many requests' => [429, SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE],
            'server error'      => [500, SpanExporterInterface::STATUS_FAILED_RETRYABLE],
            'timeout'           => [503, SpanExporterInterface::STATUS_FAILED_RETRYABLE],
            'bad gateway'       => [502, SpanExporterInterface::STATUS_FAILED_RETRYABLE],
        ];
    }

    /**
     * @dataProvider clientExceptionsShouldDecideReturnCodeDataProvider
     */
    public function test_client_exceptions_should_decide_return_code($exception, $expected): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willThrowException($exception);

        /** @var ClientInterface $client */
        $exporter = new Exporter(Exporter::createTransport(new PsrTransportFactory($client, new HttpFactory(), new HttpFactory())));

        $this->assertEquals(
            min($expected, 1),
            $exporter->export([new SpanData()])->await(),
        );
    }

    public function clientExceptionsShouldDecideReturnCodeDataProvider(): array
    {
        return [
            'client'    => [
                $this->createMock(ClientExceptionInterface::class),
                SpanExporterInterface::STATUS_FAILED_RETRYABLE,
            ],
            'network'   => [
                $this->createMock(NetworkExceptionInterface::class),
                SpanExporterInterface::STATUS_FAILED_RETRYABLE,
            ],
        ];
    }

    /**
     * @dataProvider exporterEndpointDataProvider
     */
    public function test_exporter_with_config_via_env_vars(?string $endpoint, string $expectedEndpoint): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'ff'),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $this->setEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT, $endpoint);
        $this->setEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_HEADERS, 'x-auth-header=tomato');
        $this->setEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_COMPRESSION, 'gzip');

        $client = new Client(['handler' => $stack]);
        $exporter = new Exporter(Exporter::createTransport(new PsrTransportFactory($client, new HttpFactory(), new HttpFactory())));

        $exporter->export([new SpanData()])->await();

        $request = $container[0]['request'];

        $this->assertEquals($expectedEndpoint, $request->getUri()->__toString());
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals(['tomato'], $request->getHeader('x-auth-header'));
        $this->assertEquals(['gzip'], $request->getHeader('Content-Encoding'));
        $this->assertEquals(['application/x-protobuf'], $request->getHeader('content-type'));
        $this->assertNotEquals(0, strlen($request->getBody()->getContents()));
    }

    public function exporterEndpointDataProvider(): array
    {
        return [
            'Default Endpoint' => [null, 'https://localhost:4318/v1/traces'],
            'Custom Endpoint' => ['https://otel-collector:4318/custom/path', 'https://otel-collector:4318/custom/path'],
            'Insecure Endpoint' => ['http://api.example.com:80/v1/traces', 'http://api.example.com/v1/traces'],
            //'Without Path' => ['https://api.example.com', 'https://api.example.com/v1/traces'],
            // The HTTP exporter is not defined in the spec to accept endpoints without scheme
            //'Without Scheme' => ['localhost:4318', 'https://localhost:4318/v1/traces'],
        ];
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_should_be_ok_to_exporter_empty_spans_collection(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willReturn(new Response(200));

        $this->assertEquals(
            SpanExporterInterface::STATUS_SUCCESS,
            (new Exporter(Exporter::createTransport(new PsrTransportFactory(
                $client,
                new HttpFactory(),
                new HttpFactory(),
            ))))->export([])->await(),
        );
    }

    /**
     * @testdox Exporter Refuses OTLP/JSON Protocol
     * @link https://github.com/open-telemetry/opentelemetry-specification/issues/786
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_fails_exporter_refuses_otlp_json(): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_PROTOCOL', 'http/json');

        $this->expectException(\InvalidArgumentException::class);

        new Exporter(Exporter::createTransport(new PsrTransportFactory(
            $this->getClientInterfaceMock(),
            $this->getRequestFactoryInterfaceMock(),
            $this->getStreamFactoryInterfaceMock(),
        )));
    }

    /**
     * @testdox Exporter Refuses Invalid Endpoint
     * @dataProvider exporterInvalidEndpointDataProvider
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_exporter_refuses_invalid_endpoint($endpoint): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_ENDPOINT', $endpoint);

        $this->expectException(\InvalidArgumentException::class);

        new Exporter(Exporter::createTransport(new PsrTransportFactory(
            $this->getClientInterfaceMock(),
            $this->getRequestFactoryInterfaceMock(),
            $this->getStreamFactoryInterfaceMock(),
        )));
    }

    public function exporterInvalidEndpointDataProvider(): array
    {
        return [
            'Not a url' => ['not a url'],
            'Grpc Scheme' => ['grpc://localhost:4317'],
        ];
    }

    public function test_from_connection_string(): void
    {
        $this->assertNotSame(
            Exporter::fromConnectionString(),
            Exporter::fromConnectionString()
        );
    }
}
