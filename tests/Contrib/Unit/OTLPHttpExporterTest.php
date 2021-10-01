<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use OpenTelemetry\Contrib\OtlpHttp\Exporter;
use OpenTelemetry\SDK\Trace\Test\SpanData;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;

class OTLPHttpExporterTest extends TestCase
{

    /**
     * @after
     */
    public function cleanUpEnvVars(): void
    {

        // Clear all env vars
        putenv('OTEL_EXPORTER_OTLP_ENDPOINT');
        putenv('OTEL_EXPORTER_OTLP_PROTOCOL');
        putenv('OTEL_EXPORTER_OTLP_CERTIFICATE');
        putenv('OTEL_EXPORTER_OTLP_HEADERS');
        putenv('OTEL_EXPORTER_OTLP_COMPRESSION');
        putenv('OTEL_EXPORTER_OTLP_TIMEOUT');
    }
    /**
     * @test
     * @dataProvider exporterResponseStatusesDataProvider
     */
    public function exporterResponseStatuses($responseStatus, $expected)
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willReturn(
            new Response($responseStatus)
        );
        /** @var ClientInterface $client */
        $exporter = new Exporter($client, new HttpFactory(), new HttpFactory());

        $this->assertEquals(
            $expected,
            $exporter->export([new SpanData()])
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
        $client = $this->createMock(ClientInterface::class);
        $client->method('sendRequest')->willThrowException($exception);

        /** @var ClientInterface $client */
        $exporter = new Exporter($client, new HttpFactory(), new HttpFactory());

        $this->assertEquals(
            $expected,
            $exporter->export([new SpanData()])
        );
    }

    public function clientExceptionsShouldDecideReturnCodeDataProvider()
    {
        return [
            'client'    => [
                $this->createMock(ClientExceptionInterface::class),
                Exporter::FAILED_RETRYABLE,
            ],
            'network'   => [
                $this->createMock(NetworkExceptionInterface::class),
                Exporter::FAILED_RETRYABLE,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider processHeadersDataHandler
     */
    public function testProcessHeaders($input, $expected)
    {
        $headers = (new Exporter(new Client(), new HttpFactory(), new HttpFactory()))->processHeaders($input);

        $this->assertEquals($expected, $headers);
    }

    public function processHeadersDataHandler()
    {
        return [
            'No Headers' => ['', []],
            'Empty Header' => ['empty=', ['empty' => '']],
            'One Header' => ['header-1=one', ['header-1' => 'one']],
            'Two Headers' => ['header-1=one,header-2=two', ['header-1' => 'one', 'header-2' => 'two']],
            'Two Equals' => ['header-1=bWFkZSB5b3UgbG9vaw==,header-2=two', ['header-1' => 'bWFkZSB5b3UgbG9vaw==', 'header-2' => 'two']],
            'Unicode' => ['héader-1=one', ['héader-1' => 'one']],
        ];
    }

    /**
     * @test
     * @dataProvider invalidHeadersDataHandler
     */
    public function testInvalidHeaders($input)
    {
        $this->expectException(\InvalidArgumentException::class);
        $headers = (new Exporter(new Client(), new HttpFactory(), new HttpFactory()))->processHeaders($input);
    }

    public function invalidHeadersDataHandler()
    {
        return [
            '#1' => ['a:b,c'],
            '#2' => ['a,,l'],
            '#3' => ['header-1'],
        ];
    }

    /**
     * @test
     * @dataProvider exporterEndpointDataProvider
     */
    public function testExporterWithConfigViaEnvVars($endpoint, $expectedEndpoint)
    {
        $mock = new MockHandler([
            new Response(200, [], 'ff'),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        putenv("OTEL_EXPORTER_OTLP_ENDPOINT=$endpoint");
        putenv('OTEL_EXPORTER_OTLP_HEADERS=x-auth-header=tomato');
        putenv('OTEL_EXPORTER_OTLP_COMPRESSION=gzip');

        $client = new Client(['handler' => $stack]);
        $exporter = new Exporter($client, new HttpFactory(), new HttpFactory());

        $exporter->export([new SpanData()]);

        $request = $container[0]['request'];

        $this->assertEquals($expectedEndpoint, $request->getUri()->__toString());
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals(['tomato'], $request->getHeader('x-auth-header'));
        $this->assertEquals(['gzip'], $request->getHeader('Content-Encoding'));
        $this->assertEquals(['application/x-protobuf'], $request->getHeader('content-type'));
        $this->assertNotEquals(0, strlen($request->getBody()->getContents()));
    }

    public function exporterEndpointDataProvider()
    {
        return [
            'Default Endpoint' => ['', 'https://localhost:55681/v1/traces'],
            'Custom Endpoint' => ['https://otel-collector:4317/custom/path', 'https://otel-collector:4317/custom/path'],
            'Insecure Endpoint' => ['http://api.example.com:80/v1/traces', 'http://api.example.com/v1/traces'],
            //'Without Path' => ['https://api.example.com', 'https://api.example.com/v1/traces'] # TODO: Support a default path of /v1/traces
        ];
    }

    /**
     * @test
     */
    public function shouldBeOkToExporterEmptySpansCollection()
    {
        $this->assertEquals(
            Exporter::SUCCESS,
            (new Exporter(new Client(), new HttpFactory(), new HttpFactory()))->export([])
        );
    }
    /**
     * @test
     */
    public function failsIfNotRunning()
    {
        $exporter = new Exporter(new Client(), new HttpFactory(), new HttpFactory());
        $span = $this->createMock(SpanData::class);
        $exporter->shutdown();

        $this->assertSame(Exporter::FAILED_NOT_RETRYABLE, $exporter->export([$span]));
    }

    /**
     * @test
     * @testdox Exporter Refuses OTLP/JSON Protocol
     * https://github.com/open-telemetry/opentelemetry-specification/issues/786
     */
    public function failsExporterRefusesOTLPJson()
    {
        putenv('OTEL_EXPORTER_OTLP_PROTOCOL=http/json');

        $this->expectException(\InvalidArgumentException::class);
        $exporter = new Exporter(new Client(), new HttpFactory(), new HttpFactory());
    }

    /**
     * @testdox Exporter Refuses Invalid Endpoint
     */
    public function testExporterRefusesInvalidEndpoint()
    {
        putenv('OTEL_EXPORTER_OTLP_ENDPOINT=not a url');

        $this->expectException(\InvalidArgumentException::class);
        $exporter = new Exporter(new Client(), new HttpFactory(), new HttpFactory());
    }
}
