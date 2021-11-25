<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client as MockClient;
use OpenTelemetry\Contrib\Otlp\ConfigOpts;
use OpenTelemetry\Contrib\OtlpHttp\Exporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\SDK\Unit\Trace\SpanExporter\AbstractExporterTest;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;

class OTLPHttpExporterTest extends AbstractExporterTest
{
    use EnvironmentVariables;
    use UsesHttpClientTrait;

    private ConfigOpts $config;
    private MockClient $mockClient;

    public function setUp(): void
    {
        $this->mockClient = new MockClient();
        $this->config = (new ConfigOpts());
    }

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function createExporter(): SpanExporterInterface
    {
        return new Exporter();
    }

    /**
     * @dataProvider exporterResponseStatusDataProvider
     */
    public function testExporterResponseStatus($responseStatus, $expected): void
    {
        $this->mockClient->addResponse(new Response($responseStatus));

        $exporter = new Exporter(null, $this->mockClient);

        $this->assertEquals(
            $expected,
            $exporter->export([new SpanData()])
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
    public function testClientExceptionsShouldDecideReturnCode($exception, $expected): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willThrowException($exception);

        /** @var ClientInterface $client */
        $exporter = new Exporter(null, $client);

        $this->assertEquals(
            $expected,
            $exporter->export([new SpanData()])
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
    public function testExporterWithConfigViaEnvVars(?string $endpoint, string $expectedEndpoint)
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_ENDPOINT', $endpoint);
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_HEADERS', 'x-auth-header=tomato');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_COMPRESSION', 'gzip');

        $exporter = new Exporter(null, $this->mockClient);

        $exporter->export([new SpanData()]);

        $request = $this->mockClient->getRequests()[0];

        $this->assertEquals($expectedEndpoint, $request->getUri()->__toString());
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals(['tomato'], $request->getHeader('x-auth-header'));
        $this->assertEquals(['gzip'], $request->getHeader('Content-Encoding'));
        $this->assertEquals(['application/x-protobuf'], $request->getHeader('content-type'));
        $request->getBody()->rewind();
        $this->assertNotEquals(0, $request->getBody()->getSize());
    }

    public function exporterEndpointDataProvider(): array
    {
        return [
            'Default Endpoint' => ['', 'http://localhost:4318/v1/traces'],
            'Custom Endpoint' => ['https://otel-collector:4318/custom/path', 'https://otel-collector:4318/custom/path'],
            'Insecure Endpoint' => ['http://api.example.com:80/v1/traces', 'http://api.example.com/v1/traces'],
            'Without Path' => ['https://api.example.com', 'https://api.example.com/v1/traces'],
            'Without Scheme' => ['localhost:4318', 'https://localhost:4318/v1/traces'],
        ];
    }

    /**
     * @test
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function shouldBeOkToExporterEmptySpansCollection(): void
    {
        $this->assertEquals(
            SpanExporterInterface::STATUS_SUCCESS,
            (new Exporter($this->config))->export([])
        );
    }

    /**
     * @test
     * @testdox Exporter Refuses OTLP/JSON Protocol
     * https://github.com/open-telemetry/opentelemetry-specification/issues/786
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function failsExporterRefusesOTLPJson(): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_PROTOCOL', 'http/json');

        $this->expectException(\InvalidArgumentException::class);

        new Exporter($this->config);
    }

    /**
     * @testdox Exporter Refuses Invalid Endpoint
     * @dataProvider exporterInvalidEndpointDataProvider
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function testExporterRefusesInvalidEndpoint($endpoint): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_ENDPOINT', $endpoint);

        $this->expectException(\InvalidArgumentException::class);

        new Exporter($this->config);
    }

    public function exporterInvalidEndpointDataProvider(): array
    {
        return [
            'Not a url' => ['not a url'],
            'Grpc Scheme' => ['grpc://localhost:4317'],
        ];
    }
}
