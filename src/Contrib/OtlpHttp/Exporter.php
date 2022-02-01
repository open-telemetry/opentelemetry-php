<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use InvalidArgumentException;
use Nyholm\Dsn\DsnParser;
use OpenTelemetry\Contrib\Otlp\ExporterTrait;
use OpenTelemetry\Contrib\Otlp\SpanConverter;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
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
        SpanConverter $spanConverter = null
    ) {
        // Set default values based on presence of env variable
        $this->setEndpointUrl(
            $this->validateEndpoint(
                $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_ENDPOINT, 'https://localhost:4318/v1/traces')
            )
        );
        $this->headers = $this->processHeaders($this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_HEADERS, ''));
        $this->compression = $this->getStringFromEnvironment(Env::OTEL_EXPORTER_OTLP_COMPRESSION, 'none');

        $this->setClient($client);
        $this->setRequestFactory($requestFactory);
        $this->setStreamFactory($streamFactory);
        $this->setSpanConverter($spanConverter ?? new SpanConverter());

        if ((getenv(Env::OTEL_EXPORTER_OTLP_PROTOCOL) ?: 'http/protobuf') !== 'http/protobuf') {
            throw new InvalidArgumentException('Invalid OTLP Protocol Specified');
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

    /**
     * processHeaders converts comma separated headers into an array
     */
    public function processHeaders(?string $headers): array
    {
        if (empty($headers)) {
            return [];
        }

        $pairs = explode(',', $headers);

        $metadata = [];
        foreach ($pairs as $pair) {
            $kv = explode('=', $pair, 2);

            if (count($kv) !== 2) {
                throw new InvalidArgumentException('Invalid headers passed');
            }

            [$key, $value] = $kv;

            $metadata[$key] = $value;
        }

        return $metadata;
    }

    /**
     * validateEndpoint does two functions, firstly checks that the endpoint is valid
     *  secondly it appends https:// and /v1/traces should they have been omitted
     */
    private function validateEndpoint(string $endpoint): string
    {
        $dsn = DsnParser::parseUrl($endpoint);

        if ($dsn->getScheme() === null) {
            $dsn = $dsn->withScheme('https');
        } elseif (!($dsn->getScheme() === 'https' || $dsn->getScheme() === 'http')) {
            throw new InvalidArgumentException('Expected scheme of http or https, given: ' . $dsn->getScheme());
        }

        if ($dsn->getPath() === null) {
            return (string) $dsn->withPath('/v1/traces');
        }

        return (string) $dsn;
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
}
