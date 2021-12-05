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
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 *
 * This is an experimental, non-supported exporter.
 * It will send PHP Otel trace data end to end across the internet to a functional backend.
 * Needs a license key to connect.  For a free account/key, go to: https://newrelic.com/signup/
 */
class Exporter implements SpanExporterInterface
{
    use UsesSpanConverterTrait;
    use HttpSpanExporterTrait;

    private const REQUEST_METHOD = 'POST';
    private const HEADER_CONTENT_TYPE = 'content-type';
    private const HEADER_API_KEY = 'Api-Key';
    private const HEADER_DATA_FORMAT = 'Data-Format';
    private const HEADER_DATA_FORMAT_VERSION = 'Data-Format-Version';
    private const VALUE_CONTENT_TYPE = 'application/json';
    private const VALUE_DATA_FORMAT = 'zipkin';
    private const VALUE_DATA_FORMAT_VERSION = '2';

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
        $this->setLicenseKey($licenseKey);
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
            $this->convertSpanCollection($spans),
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
            ->withHeader(self::HEADER_CONTENT_TYPE, self::VALUE_CONTENT_TYPE)
            ->withAddedHeader(self::HEADER_API_KEY, $this->getLicenseKey())
            ->withAddedHeader(self::HEADER_DATA_FORMAT, self::VALUE_DATA_FORMAT)
            ->withAddedHeader(self::HEADER_DATA_FORMAT_VERSION, self::VALUE_DATA_FORMAT_VERSION);
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args): Exporter
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

    private function getLicenseKey(): string
    {
        return $this->licenseKey;
    }

    public function setLicenseKey(string $licenseKey): void
    {
        $this->licenseKey = $licenseKey;
    }
}
