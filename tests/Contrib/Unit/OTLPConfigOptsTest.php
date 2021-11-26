<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use InvalidArgumentException;
use OpenTelemetry\Contrib\Otlp\ConfigOpts;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

use Psr\Http\Message\StreamFactoryInterface;

class OTLPConfigOptsTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function testHappyConfigOps(): void
    {
        $opts = new ConfigOpts();

        $opts->withEndpoint('https://api.example.com:1337/v1/trace')
            ->withHeaders('X-Auth-Wibble=foo,X-Dataset=bar')
            ->withProtocol('http/protobuf')
            ->withInsecure()
            ->withCompression()
            ->withTimeout(10);

        $this->assertSame('https://api.example.com:1337/v1/trace', $opts->getEndpoint());
        $this->assertSame('http/protobuf', $opts->getProtocol());
        $this->assertSame([
                'X-Auth-Wibble' => 'foo',
                'X-Dataset' => 'bar',
            ], $opts->getHeaders());
        $this->assertTrue($opts->getInsecure());
        $this->assertSame('', $opts->getCertificateFile());
        $this->assertSame('gzip', $opts->getCompression());
        $this->assertSame(10, $opts->getTimeout());
    }

    public function testRefusesInvalidHeaders(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ConfigOpts())->withHeaders('foo');
    }

    public function testSetConfigWithEnvironmentVariables(): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_HEADERS', 'x-aaa=foo,x-bbb=barf');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_INSECURE', 'true');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_CERTIFICATE', '/path/to/cacert');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_COMPRESSION', 'gzip');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_TIMEOUT', '20');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_ENDPOINT', 'localhost:4317');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_PROTOCOL', 'grpc');

        $opts = new ConfigOpts();

        $this->assertSame([
            'x-aaa' => 'foo',
            'x-bbb' => 'barf',
        ], $opts->getHeaders());
        $this->assertTrue($opts->getInsecure());
        $this->assertSame('/path/to/cacert', $opts->getCertificateFile());
        $this->assertSame('gzip', $opts->getCompression());
        $this->assertSame(20, $opts->getTimeout());
        $this->assertSame('localhost:4317', $opts->getEndpoint());
        $this->assertSame('grpc', $opts->getProtocol());
        $this->assertTrue($opts->getInsecure());
    }

    /**
     * @dataProvider protocolProvider
     */
    public function testSetProtocol(string $protocol): void
    {
        $opts = (new ConfigOpts())->withProtocol($protocol);
        $this->assertSame($protocol, $opts->getProtocol());
    }

    public function protocolProvider(): array
    {
        return [
            'protobuf' => ['http/protobuf'],
            'grpc' => ['grpc'],
        ];
    }

    /**
     * @dataProvider processHeadersDataHandler
     */
    public function testProcessHeaders($input, $expected): void
    {
        $opts = (new ConfigOpts())->withHeaders($input);

        $this->assertEquals($expected, $opts->getHeaders());
    }

    public function processHeadersDataHandler(): array
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
    public function testInvalidHeaders($input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new ConfigOpts())->withHeaders($input);
    }

    public function invalidHeadersDataHandler(): array
    {
        return [
            '#1' => ['a:b,c'],
            '#2' => ['a,,l'],
            '#3' => ['header-1'],
        ];
    }

    public function testUsesHttpDiscoveryForDefaults(): void
    {
        $config = (new ConfigOpts());
        $this->assertInstanceOf(ClientInterface::class, $config->getHttpClient());
        $this->assertInstanceOf(RequestFactoryInterface::class, $config->getHttpRequestFactory());
        $this->assertInstanceOf(StreamFactoryInterface::class, $config->getHttpStreamFactory());
    }

    public function testAcceptsConcretePsrImplementations(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $config = (new ConfigOpts())
            ->withHttpClient($client)
            ->withHttpRequestFactory($requestFactory)
            ->withHttpStreamFactory($streamFactory);

        $this->assertInstanceOf(ClientInterface::class, $config->getHttpClient());
        $this->assertInstanceOf(RequestFactoryInterface::class, $config->getHttpRequestFactory());
        $this->assertInstanceOf(StreamFactoryInterface::class, $config->getHttpStreamFactory());
    }

    public function testGrpcTraceServiceClient(): void
    {
        $client = $this->createMock(TraceServiceClient::class);
        $config = (new ConfigOpts())->withGrpcTraceServiceClient($client);
        $this->assertSame($client, $config->getGrpcTraceServiceClient());
    }
}
