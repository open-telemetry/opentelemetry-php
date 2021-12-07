<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use JsonException;
use OpenTelemetry\SDK\Trace\Behavior\HttpSpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 */
class Exporter implements SpanExporterInterface
{
    use UsesSpanConverterTrait;
    use HttpSpanExporterTrait;

    private const REQUEST_METHOD = 'POST';
    private const HEADER_CONTENT_TYPE = 'content-type';
    private const VALUE_CONTENT_TYPE = 'application/json';

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

    /**
     * @throws JsonException
     */
    protected function serializeTrace(iterable $spans): string
    {
        return json_encode(
            $this->getSpanConverter()->convert($spans),
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @throws JsonException
     */
    protected function marshallRequest(iterable $spans): RequestInterface
    {
        return $this->createRequest(self::REQUEST_METHOD)
            ->withBody(
                $this->createStream(
                    $this->serializeTrace($spans)
                )
            )
            ->withHeader(self::HEADER_CONTENT_TYPE, self::VALUE_CONTENT_TYPE);
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
