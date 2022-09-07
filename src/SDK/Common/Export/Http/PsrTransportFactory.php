<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use const FILTER_VALIDATE_URL;
use function filter_var;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class PsrTransportFactory implements TransportFactoryInterface
{
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public function create(
        string $endpoint,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null
    ): TransportInterface {
        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf('Invalid endpoint url "%s"', $endpoint));
        }

        return new PsrTransport(
            $this->client,
            $this->requestFactory,
            $this->streamFactory,
            $endpoint,
            $headers,
            (array) $compression,
            $retryDelay,
            $maxRetries,
        );
    }
}
