<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use InvalidArgumentException;
use Nyholm\Dsn\DsnParser;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Trace;
use OpenTelemetry\SDK\Trace\Behavior\HttpSpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Exporter implements Trace\SpanExporterInterface
{
    use EnvironmentVariablesTrait;
    use UsesSpanConverterTrait;
    use HttpSpanExporterTrait;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private string $insecure;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private string $certificateFile;

    private array $headers;

    private string $compression;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private int $timeout;

    private SpanConverter $spanConverter;

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
        $endpointUrl = $this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_ENDPOINT', 'https://localhost:4318/v1/traces');
        $this->protocol = $this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_PROTOCOL', 'http/protobuf');
        // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
        // $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: 'none';
        $this->headers = $this->processHeaders($this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_HEADERS', ''));
        $this->compression = $this->getStringFromEnvironment('OTEL_EXPORTER_OTLP_COMPRESSION', 'none');
        // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
        // $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: 10;

        $this->setClient($client);
        $this->setRequestFactory($requestFactory);
        $this->setStreamFactory($streamFactory);

        $this->spanConverter =  $spanConverter ?? new SpanConverter();
        $this->endpointUrl = $this->validateEndpoint($endpointUrl);

        if ((getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'http/protobuf') !== 'http/protobuf') {
            throw new InvalidArgumentException('Invalid OTLP Protocol Specified');
        }
    }

    /** @inheritDoc */
    public function doExport(iterable $spans): int
    {
        $bytes = (new ExportTraceServiceRequest([
            'resource_spans' => [$this->spanConverter->as_otlp_resource_span($spans)],
        ]))->serializeToString();

        try {
            $request = $this->getRequestFactory()
                ->createRequest('POST', $this->getEndpointUrl())
                ->withHeader('content-type', 'application/x-protobuf');

            foreach ($this->headers as $header => $value) {
                $request = $request->withHeader($header, $value);
            }

            if ($this->compression === 'gzip') {
                // TODO: Add Tests
                $body = $this->getStreamFactory()->createStream(gzencode($bytes));
                $request = $request->withHeader('Content-Encoding', 'gzip');
            } else {
                $body = $this->getStreamFactory()->createStream($bytes);
            }

            $response = $this->getClient()->sendRequest($request->withBody($body));
        } catch (RequestExceptionInterface $e) {
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
     *
     * @param string $endpoint
     * @return string
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

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null)
    {
        return new Exporter(
            HttpClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }

    public static function create()
    {
        return self::fromConnectionString();
    }
}
