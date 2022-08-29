<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use function assert;
use BadMethodCallException;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use Throwable;
use function in_array;
use function time_nanosleep;

final class PsrTransport implements TransportInterface
{
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    private string $endpoint;
    private array $headers;
    private ?string $compression;
    private int $retryDelay;
    private int $maxRetries;

    private bool $closed = false;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $endpoint,
        array $headers,
        ?string $compression,
        int $retryDelay,
        int $maxRetries
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->endpoint = $endpoint;
        $this->headers = $headers;
        $this->compression = $compression;
        $this->retryDelay = $retryDelay;
        $this->maxRetries = $maxRetries;
    }

    public function send(string $payload, string $contentType, ?CancellationInterface $cancellation = null): FutureInterface
    {
        if ($this->closed) {
            return new ErrorFuture(new BadMethodCallException('Transport closed'));
        }

        $request = $this->requestFactory
            ->createRequest('POST', $this->endpoint)
            ->withBody($this->streamFactory->createStream($payload));

        $request = $request->withHeader('Content-Type', $contentType);
        if ($this->compression !== null) {
            $request = $request->withBody(PsrUtils::encodeStream($request->getBody(), $this->compression, $this->streamFactory));
            $request = $request->withHeader('Content-Encoding', $this->compression);
        }
        foreach ($this->headers as $header => $value) {
            $request = $request->withAddedHeader($header, $value);
        }

        for ($retries = 0;; $retries++) {
            $response = null;
            $e = null;

            try {
                $response = $this->client->sendRequest($request);
                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    break;
                }

                if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500 && !in_array($response->getStatusCode(), [408, 429], true)) {
                    return new ErrorFuture(new RuntimeException($response->getReasonPhrase(), $response->getStatusCode()));
                }
            } catch (NetworkExceptionInterface $e) {
            } catch (Throwable $e) {
                return new ErrorFuture($e);
            }

            if ($retries + 1 === $this->maxRetries) {
                return new ErrorFuture(new RuntimeException('Export retry limit exceeded', 0, $e));
            }

            $delay = PsrUtils::retryDelay($retries, $this->retryDelay, $response);
            $sec = (int) $delay;
            $nsec = (int) (($delay - $sec) * 1e9);

            /** @psalm-suppress ArgumentTypeCoercion */
            if (time_nanosleep($sec, $nsec) !== true) {
                return new ErrorFuture(new RuntimeException('Export cancelled', 0, $e));
            }
        }

        assert(isset($response));

        try {
            $body = $response->getBody();
            if (($encoding = $response->getHeaderLine('Content-Encoding')) !== '') {
                $body = PsrUtils::decodeStream($body, $encoding, $this->streamFactory);
            }

            return new CompletedFuture($body->__toString());
        } catch (Throwable $e) {
            return new ErrorFuture($e);
        }
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return true;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return !$this->closed;
    }
}
