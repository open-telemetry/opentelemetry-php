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
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
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
    public function test_invalid_endpoint_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createMock(ClientInterface::class);
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $factory->create('localhost');
    }

    public function test_send_propagates_body_and_content_type(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturnCallback(function (RequestInterface $request): ResponseInterface {
            $this->assertSame('abc', (string) $request->getBody());
            $this->assertSame('text/plain', $request->getHeaderLine('Content-Type'));

            return new Response();
        });
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');

        $transport->send('abc', 'text/plain');
    }

    public function test_send_applies_compression(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturnCallback(function (RequestInterface $request): ResponseInterface {
            $this->assertSame('abc', gzdecode((string) $request->getBody()));
            $this->assertSame('gzip', $request->getHeaderLine('Content-Encoding'));

            return new Response();
        });
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost', [], 'gzip');

        $transport->send('abc', 'text/plain');
    }

    public function test_send_sets_headers(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturnCallback(function (RequestInterface $request): ResponseInterface {
            $this->assertSame('bar', $request->getHeaderLine('x-foo'));

            return new Response();
        });
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost', ['x-foo' => 'bar']);

        $transport->send('abc', 'text/plain');
    }

    public function test_send_returns_response_body(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, [], 'abc'));
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');

        $this->assertSame('abc', $transport->send('', 'text/plain')->await());
    }

    public function test_send_decodes_response_body(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, ['Content-Encoding' => 'gzip'], gzencode('abc')));
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');

        $this->assertSame('abc', $transport->send('', 'text/plain')->await());
    }

    public function test_send_decode_unknown_encoding_returns_error(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, ['Content-Encoding' => 'invalid'], ''));
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');

        $response = $transport->send('', 'text/plain');

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_send_status_code4xx_returns_error(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(new Response(403));
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');

        $response = $transport->send('', 'text/plain');

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_send_status_code5xx_retries(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))->method('sendRequest')->willReturnOnConsecutiveCalls(
            new Response(500),
            new Response(200, [], 'abc')
        );
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost', [], null, 10., 1);

        $this->assertSame('abc', $transport->send('', 'text/plain')->await());
    }

    public function test_send_returns_error_if_retry_limit_exceeded(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(new Response(500));
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost', [], null, 10., 100, 1);

        $response = $transport->send('', 'text/plain');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('retry limit');
        $response->await();
    }

    public function test_send_exception_returns_error(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willThrowException(new Exception());
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');

        $response = $transport->send('', 'text/plain');

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_shutdown_returns_true(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');

        $this->assertTrue($transport->shutdown());
    }

    public function test_force_flush_returns_true(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');

        $this->assertTrue($transport->forceFlush());
    }

    public function test_send_closed_returns_error(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');
        $transport->shutdown();

        $response = $transport->send('', 'text/plain');

        $this->expectException(Exception::class);
        $response->await();
    }

    public function test_shutdown_closed_returns_false(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');
        $transport->shutdown();

        $this->assertFalse($transport->shutdown());
    }

    public function test_force_flush_closed_returns_false(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $factory = new PsrTransportFactory($client, new HttpFactory(), new HttpFactory());
        $transport = $factory->create('http://localhost');
        $transport->shutdown();

        $this->assertFalse($transport->forceFlush());
    }
}
