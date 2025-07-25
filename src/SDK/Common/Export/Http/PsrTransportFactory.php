<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use const FILTER_VALIDATE_URL;
use function filter_var;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class PsrTransportFactory implements TransportFactoryInterface
{
    public function __construct(
        private ?ClientInterface $client = null,
        private ?RequestFactoryInterface $requestFactory = null,
        private ?StreamFactoryInterface $streamFactory = null,
    ) {
    }

    /**
     * @phan-suppress PhanTypeMismatchArgumentNullable
     */
    #[\Override]
    public function create(
        string $endpoint,
        string $contentType,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null,
    ): TransportInterface {
        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf('Invalid endpoint url "%s"', $endpoint));
        }
        assert(!empty($endpoint));

        $this->client ??= Discovery::find([
            'timeout' => $timeout,
        ]);
        $this->requestFactory ??= Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory ??= Psr17FactoryDiscovery::findStreamFactory();

        return new PsrTransport(
            $this->client,
            $this->requestFactory,
            $this->streamFactory,
            $endpoint,
            $contentType,
            $headers,
            PsrUtils::compression($compression),
            $retryDelay,
            $maxRetries,
        );
    }

    /**
     * @deprecated
     */
    public static function discover(): self
    {
        return new self(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        );
    }
}
