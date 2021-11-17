<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use JsonException;
use OpenTelemetry\SDK\Trace;
use OpenTelemetry\SDK\Trace\Behavior\HttpSpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 */
class Exporter implements Trace\SpanExporterInterface
{
    use UsesSpanConverterTrait;
    use HttpSpanExporterTrait;

    public function __construct(
        $name,
        string $endpointUrl,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        SpanConverter $spanConverter = null
    ) {
        $this->setEndpointUrl($endpointUrl);
        $this->setClient($client);
        $this->setRequestFactory($requestFactory);
        $this->setStreamFactory($streamFactory);
        $this->setSpanConverter($spanConverter ?? new SpanConverter($name));
    }

    /** @inheritDoc */
    public function doExport(iterable $spans): int
    {
        try {
            $body = $this->getStreamFactory()->createStream(
                json_encode(
                    $this->convertSpanCollection($spans),
                    JSON_THROW_ON_ERROR
                )
            );
            $request = $this->getRequestFactory()
                ->createRequest('POST', $this->endpointUrl)
                ->withBody($body)
                ->withHeader('content-type', 'application/json');

            $response = $this->getClient()->sendRequest($request);
        } catch (RequestExceptionInterface | JsonException $e) {
            return self::STATUS_FAILED_NOT_RETRYABLE;
        } catch (NetworkExceptionInterface | ClientExceptionInterface $e) {
            return self::STATUS_FAILED_RETRYABLE;
        }

        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            return self::STATUS_FAILED_NOT_RETRYABLE;
        }

        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            return self::STATUS_FAILED_RETRYABLE;
        }

        return self::STATUS_SUCCESS;
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args = null)
    {
        return new Exporter(
            $name,
            $endpointUrl,
            HttpClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }
}
