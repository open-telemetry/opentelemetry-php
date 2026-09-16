<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use Http\Discovery\Psr17FactoryDiscovery;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

/**
 * PSR-7/PSR-18 HTTP transport for OTLP/HTTP exporters.
 *
 * ### Response body size limiting (issue #1932)
 *
 * All response body reads are funnelled through
 * {@see PsrUtils::readBodyWithSizeLimit()}, which caps consumption at 4 MiB.
 * This prevents a misconfigured or malicious collector from causing unbounded
 * memory growth in the PHP process.
 */
final class PsrTransport
{
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private string $endpoint;
    private string $contentType;

    /** @var array<string,string> */
    private array $headers;
    private string $compression;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $endpoint,
        string $contentType,
        array $headers = [],
        string $compression = 'none'
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->endpoint = $endpoint;
        $this->contentType = $contentType;
        $this->headers = $headers;
        $this->compression = $compression;
    }

    /**
     * Send $payload to the OTLP endpoint and return a Future that resolves to
     * the (size-limited, decoded) response body string.
     *
     * @param string $payload Serialised protobuf or JSON export request.
     *
     * @return FutureInterface<string>
     */
    public function send(string $payload, ?CancellationInterface $cancellation = null): FutureInterface
    {
        try {
            $request = $this->buildRequest($payload);
            $response = $this->client->sendRequest($request);

            return new CompletedFuture($this->handleResponse($response));
        } catch (Throwable $e) {
            return new ErrorFuture($e);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildRequest(string $payload): RequestInterface
    {
        $body = $payload;

        if ($this->compression === 'gzip') {
            $body = gzencode($payload);
        }

        $stream = $this->streamFactory->createStream($body);

        $request = $this->requestFactory
            ->createRequest('POST', $this->endpoint)
            ->withBody($stream)
            ->withHeader('Content-Type', $this->contentType);

        if ($this->compression === 'gzip') {
            $request = $request->withHeader('Content-Encoding', 'gzip');
        }

        foreach ($this->headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    /**
     * Read and decode the response body, subject to the 4 MiB limit defined
     * by {@see ResponseBodySizeLimit::MAX_BYTES}.
     *
     * For non-2xx responses an exception is thrown so the exporter can apply
     * its retry / drop logic.
     *
     * @throws TransportResponseException on HTTP error status codes.
     */
    private function handleResponse(ResponseInterface $response): string
    {
        $statusCode = $response->getStatusCode();

        // Always read (and limit) the body first — we need it for error details.
        $body = PsrUtils::decode($response);

        if ($statusCode >= 200 && $statusCode < 300) {
            return $body;
        }

        throw new TransportResponseException(
            $statusCode,
            $body,
            sprintf(
                'OTLP export failed with HTTP %d. Body (up to %d bytes): %s',
                $statusCode,
                ResponseBodySizeLimit::MAX_BYTES,
                $body !== '' ? $body : '(empty)'
            )
        );
    }
}
