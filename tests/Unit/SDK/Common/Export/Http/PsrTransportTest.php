<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export\Http;

use Exception;
use GuzzleHttp\Psr7\HttpFactory;
use function gzdecode;
use function gzencode;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\Http\ResponseBodyTooLargeException;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;
use RuntimeException;

#[CoversClass(PsrTransportFactory::class)]
#[CoversClass(PsrTransport::class)]
final class PsrTransportTest extends TestCase
{
    use TestState;

    /**
     * @var MockObject&ClientInterface
     */
    private MockObject $client;
    private PsrTransportFactory $factory;

    #[\Override]
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

    /**
     * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.11.1/docs/specification.md#otlphttp-response
     */
    public function test_send_response_body_at_limit_is_returned(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, [], str_repeat('a', PsrTransport::DEFAULT_MAX_RESPONSE_BODY_SIZE)));
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $this->assertSame(PsrTransport::DEFAULT_MAX_RESPONSE_BODY_SIZE, strlen($transport->send('')->await()));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.11.1/docs/specification.md#otlphttp-response
     */
    public function test_send_response_body_over_limit_returns_error_without_retry(): void
    {
        $this->expectDiscardedResponseWarning();
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, [], str_repeat('a', PsrTransport::DEFAULT_MAX_RESPONSE_BODY_SIZE + 1)));
        $transport = $this->factory->create('http://localhost', 'text/plain', [], null, 10, 3);

        $this->expectException(ResponseBodyTooLargeException::class);
        $transport->send('')->await();
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.11.1/docs/specification.md#otlphttp-response
     */
    public function test_send_response_body_over_limit_after_decompression_returns_error(): void
    {
        $this->expectDiscardedResponseWarning();
        $body = gzencode(str_repeat('a', PsrTransport::DEFAULT_MAX_RESPONSE_BODY_SIZE + 1));
        $this->assertLessThan(PsrTransport::DEFAULT_MAX_RESPONSE_BODY_SIZE, strlen($body));
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, ['Content-Encoding' => 'gzip'], $body));
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $this->expectException(ResponseBodyTooLargeException::class);
        $transport->send('')->await();
    }

    private function expectDiscardedResponseWarning(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->once())->method('write')->with(
            LogLevel::WARNING,
            $this->stringContains('discarded'),
            $this->callback(fn (array $context): bool => $context['limit'] === PsrTransport::DEFAULT_MAX_RESPONSE_BODY_SIZE),
        );
        Logging::setLogWriter($logWriter);
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.11.1/docs/specification.md#otlphttp-response
     */
    #[DataProvider('oversizedBodies')]
    public function test_send_oversized_error_response_is_not_retried(callable $createResponse): void
    {
        $this->expectDiscardedResponseWarning();
        $this->client->expects($this->once())->method('sendRequest')->willReturn($createResponse());
        $transport = $this->factory->create('http://localhost', 'text/plain', [], null, 10, 3);

        $this->expectException(ResponseBodyTooLargeException::class);
        $transport->send('')->await();
    }

    public static function oversizedBodies(): iterable
    {
        $limit = PsrTransport::DEFAULT_MAX_RESPONSE_BODY_SIZE;

        yield '503' => [static fn () => new Response(503, [], str_repeat('a', $limit + 1))];
        yield '403' => [static fn () => new Response(403, [], str_repeat('a', $limit + 1))];
        yield '500' => [static fn () => new Response(500, [], str_repeat('a', $limit + 1))];
        yield '429 compressed' => [static fn () => new Response(429, ['Content-Encoding' => 'gzip'], gzencode(str_repeat('a', $limit + 1)))];
    }

    public function test_send_error_response_with_unreadable_body_keeps_status_error(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(403, ['Content-Encoding' => 'invalid'], 'foo'));
        $transport = $this->factory->create('http://localhost', 'text/plain');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(403);
        $transport->send('')->await();
    }

    public function test_send_decode_unknown_encoding_returns_error(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, ['Content-Encoding' => 'invalid'], 'foo'));
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
            new Response(503),
            new Response(200, [], 'abc')
        );
        $transport = $this->factory->create('http://localhost', 'text/plain', [], null, 10., 1);

        $this->assertSame('abc', $transport->send('')->await());
    }

    public function test_send_returns_error_if_retry_limit_exceeded(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willReturn(new Response(503));
        $transport = $this->factory->create('http://localhost', 'text/plain', [], null, 10., 100, 0);

        $response = $transport->send('');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('retry limit');
        $response->await();
    }

    public function test_send_number_of_retries(): void
    {
        $this->client->expects($this->exactly(4))->method('sendRequest')->willReturn(new Response(503));
        $transport = $this->factory->create('http://localhost', 'text/plain', [], null, 10., 100, 3);

        $response = $transport->send('');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('retry limit');
        $response->await();
    }

    public function test_send_retry_network_exception_returns_error(): void
    {
        $e = new class('network error') extends Exception implements NetworkExceptionInterface {
            #[\Override]
            public function getRequest(): RequestInterface
            {
                return new Request('GET', 'http://localhost');
            }
        };
        $this->client->expects($this->exactly(4))->method('sendRequest')->willThrowException($e);
        $transport = $this->factory->create('http://localhost', 'text/plain', [], null, 10., 100, 3);

        $response = $transport->send('');

        try {
            $response->await();
        } catch (Exception $e) {
            $this->assertStringContainsString('retry limit', $e->getMessage());
            $this->assertNotNull($e->getPrevious());
            $this->assertSame('network error', $e->getPrevious()->getMessage());
        }
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
