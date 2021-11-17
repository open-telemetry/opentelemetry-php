<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\ZipkinToNewrelic;

use Exception;
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
 *
 * This is an experimental, non-supported exporter.
 * It will send PHP Otel trace data end to end across the internet to a functional backend.
 * Needs a license key to connect.  For a free account/key, go to: https://newrelic.com/signup/
 */
class Exporter implements Trace\SpanExporterInterface
{
    use UsesSpanConverterTrait;
    use HttpSpanExporterTrait;

    private string $licenseKey;
    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private string $name;

    public function __construct(
        $name,
        string $endpointUrl,
        string $licenseKey,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        SpanConverter $spanConverter = null
    ) {
        // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
        // $this->name = $name;
        $this->licenseKey = $licenseKey;

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
            $request = $this->requestFactory
                ->createRequest('POST', $this->endpointUrl)
                ->withBody($body)
                ->withHeader('content-type', 'application/json')
                ->withAddedHeader('Api-Key', $this->licenseKey)
                ->withAddedHeader('Data-Format', 'zipkin')
                ->withAddedHeader('Data-Format-Version', '2');

            $response = $this->client->sendRequest($request);
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
    public static function fromConnectionString(string $endpointUrl, string $name, $args)
    {
        if (!is_string($args)) {
            throw new Exception('Invalid license key.');
        }

        return new Exporter(
            $name,
            $endpointUrl,
            $args,
            HttpClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }
}
