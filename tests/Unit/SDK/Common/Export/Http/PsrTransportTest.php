<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export\Http;

use Exception;
use GuzzleHttp\Psr7\HttpFactory;
use function gzdecode;
use function gzencode;
use InvalidArgumentException;
use Nyholm\Psr7\Response;
use OpenTelemetry\API\Common\Export\Headers;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers \OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory
 * @covers \OpenTelemetry\SDK\Common\Export\Http\PsrTransport
 */
final class PsrTransportTest extends TestCase
{
    private MockObject $client;
    private PsrTransportFactory $factory;

    public function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->factory = new PsrTransportFactory($this->client, new HttpFactory(), new HttpFactory());
    }

    public function test_invalid_endpoint_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->factory->create('localhost', 'text/plain');
    }

    public function test_send_propagates_body_and_content_type(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturnCallback(function (RequestInterface $request): ResponseInterface {
            $this->assertSame('abc', (string) $request->getBody());
            $this->assertSame('text/plain', $request->getHeaderLine('Content-Type'));

            return new Response();
        });
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $transport->send('abc');
    }

    public function test_send_applies_compression(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturnCallback(function (RequestInterface $request): ResponseInterface {
            $this->assertSame('abc', gzdecode((string) $request->getBody()));
            $this->assertSame('gzip', $request->getHeaderLine('Content-Encoding'));

            return new Response();
        });
        $transport = $this->factory->create('http://localhost', 'text/plain', [], 'gzip');

        $transport->send('abc');
    }

    public function test_send_sets_headers(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturnCallback(function (RequestInterface $request): ResponseInterface {
            $this->assertSame('bar', $request->getHeaderLine('x-foo'));
            $this->assertTrue($request->hasHeader(Headers::EXPORTER_HEADER), 'contains custom header for identifying exporter requests');

            return new Response();
        });
        $transport = $this->factory->create('http://localhost', 'text/plain', ['x-foo' => 'bar']);

        $transport->send('abc');
    }

    public function test_send_returns_response_body(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, [], 'abc'));
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $this->assertSame('abc', $transport->send('')->await());
    }

    public function test_send_decodes_response_body(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, ['Content-Encoding' => 'gzip'], gzencode('abc')));
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $this->assertSame('abc', $transport->send('')->await());
    }

    public function test_send_decode_unknown_encoding_returns_error(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, ['Content-Encoding' => 'invalid'], ''));
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $response = $transport->send('');

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_send_status_code4xx_returns_error(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(403));
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $response = $transport->send('');

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_send_status_code5xx_retries(): void
    {
        $this->client->expects($this->exactly(2))->method('sendRequest')->willReturnOnConsecutiveCalls(
            new Response(500),
            new Response(200, [], 'abc')
        );
        $transport = $this->factory->create('http://localhost', 'text/plain', [], null, 10., 1);

        $this->assertSame('abc', $transport->send('')->await());
    }

    public function test_send_returns_error_if_retry_limit_exceeded(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(500));
        $transport = $this->factory->create('http://localhost', 'text/plain', [], null, 10., 100, 1);

        $response = $transport->send('');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('retry limit');
        $response->await();
    }

    public function test_send_exception_returns_error(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willThrowException(new Exception());
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $response = $transport->send('');

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_shutdown_returns_true(): void
    {
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $this->assertTrue($transport->shutdown());
    }

    public function test_force_flush_returns_true(): void
    {
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $this->assertTrue($transport->forceFlush());
    }

    public function test_send_closed_returns_error(): void
    {
        $transport = $this->factory->create('http://localhost', 'text/plain');
        $transport->shutdown();

        $response = $transport->send('');

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_shutdown_closed_returns_false(): void
    {
        $transport = $this->factory->create('http://localhost', 'text/plain');
        $transport->shutdown();

        $this->assertFalse($transport->shutdown());
    }

    public function test_force_flush_closed_returns_false(): void
    {
        $transport = $this->factory->create('http://localhost', 'text/plain');
        $transport->shutdown();

        $this->assertFalse($transport->forceFlush());
    }
}
