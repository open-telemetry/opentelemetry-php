<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

//use Throwable;

trait HttpSpanExporterTrait
{
    use SpanExporterTrait;

    protected string $endpointUrl;

    protected ClientInterface $client;

    protected RequestFactoryInterface $requestFactory;

    protected StreamFactoryInterface $streamFactory;

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
