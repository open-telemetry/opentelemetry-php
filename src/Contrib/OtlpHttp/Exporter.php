<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use InvalidArgumentException;
use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Otlp\ExporterTrait;
use OpenTelemetry\Contrib\Otlp\SpanConverter;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolver;
use OpenTelemetry\SDK\Common\Otlp\HttpEndpointResolverInterface;
use OpenTelemetry\SDK\Trace\Behavior\HttpSpanExporterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Exporter implements SpanExporterInterface
{
    use ExporterTrait;
    use HttpSpanExporterTrait;

    private const REQUEST_METHOD = 'POST';
    private const HEADER_CONTENT_TYPE = 'content-type';
    private const HEADER_CONTENT_ENCODING = 'Content-Encoding';
    private const VALUE_CONTENT_TYPE = 'application/x-protobuf';
    private const VALUE_CONTENT_ENCODING = 'gzip';
    private const DEFAULT_ENDPOINT = 'https://localhost:4318/v1/traces';
    private const DEFAULT_COMPRESSION = 'none';
    private const OTLP_PROTOCOL = 'http/protobuf';

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private string $insecure;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private string $certificateFile;

    private array $headers;

    private string $compression;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private int $timeout;

    /**
     * Exporter constructor.
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        ?SpanConverter $spanConverter = null,
        ?HttpEndpointResolverInterface $httpEndpointResolver = null
    ) {
        $this->setEndpointUrl(
            $this->resolveEndpoint($httpEndpointResolver ?? HttpEndpointResolver::create())
        );

        $this->headers = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) ?
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_HEADERS) :
            $this->getMapFromEnvironment(Env::OTEL_EXPORTER_OTLP_HEADERS);

        $this->compression = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION) ?
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION, self::DEFAULT_COMPRESSION) :
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_COMPRESSION, self::DEFAULT_COMPRESSION);

        $this->setClient($client);
        $this->setRequestFactory($requestFactory);
        $this->setStreamFactory($streamFactory);
        $this->setSpanConverter($spanConverter ?? new SpanConverter());

        $protocol = $this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL) ?
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL, self::OTLP_PROTOCOL) :
            $this->getEnumFromEnvironment(Env::OTEL_EXPORTER_OTLP_PROTOCOL, self::OTLP_PROTOCOL);

        if ($protocol !== self::OTLP_PROTOCOL) {
            throw new InvalidArgumentException(sprintf('Invalid OTLP Protocol "%s" specified', $protocol));
        }
    }

    protected function serializeTrace(iterable $spans): string
    {
        $bytes = (new ExportTraceServiceRequest([
            'resource_spans' => $this->getSpanConverter()->convert($spans),
        ]))->serializeToString();

        // TODO: Add Tests
        return $this->shouldCompress()
            ? gzencode($bytes)
            : $bytes;
    }

    protected function marshallRequest(iterable $spans): RequestInterface
    {
        $request =  $this->createRequest(self::REQUEST_METHOD)
            ->withBody(
                $this->createStream(
                    $this->serializeTrace($spans)
                )
            )
            ->withHeader(self::HEADER_CONTENT_TYPE, self::VALUE_CONTENT_TYPE);

        foreach ($this->headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        if ($this->shouldCompress()) {
            return $request->withHeader(self::HEADER_CONTENT_ENCODING, self::VALUE_CONTENT_ENCODING);
        }

        return $request;
    }

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null): Exporter
    {
        return new Exporter(
            HttpClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }

    private function shouldCompress(): bool
    {
        return $this->compression === 'gzip' && function_exists('gzencode');
    }

    private function resolveEndpoint(HttpEndpointResolverInterface $httpEndpointResolver): string
    {
        if ($this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT)) {
            return $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT);
        }

        if ($this->hasEnvironmentVariable(Env::OTEL_EXPORTER_OTLP_ENDPOINT)) {
            return $httpEndpointResolver->resolveToString(
                $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_ENDPOINT),
                Signals::TRACE
            );
        }

        return self::DEFAULT_ENDPOINT;
    }
}
