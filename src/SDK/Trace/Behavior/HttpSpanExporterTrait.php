<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use InvalidArgumentException;
use JsonException;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Throwable;

//use Throwable;

/**
 * @phan-file-suppress PhanTypeInvalidThrowsIsInterface
 */
trait HttpSpanExporterTrait
{
    use LogsMessagesTrait;
    use SpanExporterTrait;

    protected string $endpointUrl;

    protected ClientInterface $client;

    protected RequestFactoryInterface $requestFactory;

    protected StreamFactoryInterface $streamFactory;

    abstract protected function serializeTrace(iterable $spans): string;

    abstract protected function marshallRequest(iterable $spans): RequestInterface;

    /**
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    protected function doExport(iterable $spans): int
    {
        try {
            $response = $this->dispatchSpans($spans);
        } catch (ClientExceptionInterface $e) {
            self::logError('Unable to export span(s)', ['exception' => $e]);

            return $e instanceof RequestExceptionInterface
                ? SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE
                : SpanExporterInterface::STATUS_FAILED_RETRYABLE;
        } catch (Throwable $e) {
            self::logError('Unable to export span(s)', ['exception' => $e]);

            return SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE;
        }

        if ($response->getStatusCode() >= 400) {
            self::logError('Unable to export span(s)', ['code' => $response->getStatusCode()]);

            return $response->getStatusCode() < 500
                ? SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE
                : SpanExporterInterface::STATUS_FAILED_RETRYABLE;
        }

        self::logDebug('Exported span(s)', ['spans' => $spans]);
        return SpanExporterInterface::STATUS_SUCCESS;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function dispatchSpans(iterable $spans): ResponseInterface
    {
        return $this->sendRequest(
            $this->marshallRequest($spans)
        );
    }

    private function validateEndpointUrl(string $endpointUrl): void
    {
        /**  Temporarily replacing assert with hard exception
        try {
        assert(filter_var($endpointUrl, FILTER_VALIDATE_URL));
        } catch (Throwable $e) {
        throw new InvalidArgumentException('Invalid Endpoint URL given: ' . $endpointUrl, E_WARNING, $e);
        }
         */
        if (filter_var($endpointUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid Endpoint URL given: ' . $endpointUrl);
        }
    }

    protected function createRequest(string $method, $uri = null): RequestInterface
    {
        return $this->getRequestFactory()
            ->createRequest($method, $uri ?: $this->getEndpointUrl());
    }

    /**
     * @throws ClientExceptionInterface
     */
    protected function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->getClient()->sendRequest($request);
    }

    protected function createStream(string $content = ''): StreamInterface
    {
        return $this->getStreamFactory()->createStream($content);
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    protected function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    public function getEndpointUrl(): string
    {
        return $this->endpointUrl;
    }

    protected function setEndpointUrl(string $endpointUrl): void
    {
        $this->validateEndpointUrl($endpointUrl);

        $this->endpointUrl = $endpointUrl;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    protected function setRequestFactory(RequestFactoryInterface $requestFactory): void
    {
        $this->requestFactory = $requestFactory;
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    protected function setStreamFactory(StreamFactoryInterface $streamFactory): void
    {
        $this->streamFactory = $streamFactory;
    }
}
