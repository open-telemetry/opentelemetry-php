<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use Nyholm\Dsn\DsnParser;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use OpenTelemetry\SDK\Trace;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Exporter implements Trace\SpanExporterInterface
{
    /**
     * @var string
     */
    private $endpointUrl;

    /**
     * @var string
     */
    private $protocol;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private string $insecure;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private string $certificateFile;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $compression;

    // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
    // private int $timeout;

    /**
     * @var SpanConverter
     */
    private $spanConverter;

    /**
    * @var bool
    */
    private $running = true;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

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
        $endpointUrl = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'https://localhost:4318/v1/traces';
        $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'http/protobuf';
        // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
        // $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: 'none';
        $this->headers = $this->processHeaders(getenv('OTEL_EXPORTER_OTLP_HEADERS'));
        $this->compression = getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: 'none';
        // @todo: Please, check if this code is needed. It creates an error in phpstan, since it's not used
        // $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: 10;

        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->spanConverter =  $spanConverter ?? new SpanConverter();

        if ($this->protocol != 'http/protobuf') {
            throw new InvalidArgumentException('Invalid OTLP Protocol Specified');
        }

        $this->endpointUrl = $this->validateEndpoint($endpointUrl);
    }

    /** @inheritDoc */
    public function export(iterable $spans): int
    {
        if (!$this->running) {
            return self::STATUS_FAILED_NOT_RETRYABLE;
        }

        if (empty($spans)) {
            return self::STATUS_SUCCESS;
        }

        $resourcespans = [$this->spanConverter->as_otlp_resource_span($spans)];

        $exportrequest = new ExportTraceServiceRequest([
            'resource_spans' => $resourcespans,
        ]);

        $bytes = $exportrequest->serializeToString();

        try {
            $request = $this->requestFactory
                ->createRequest('POST', $this->endpointUrl)
                ->withHeader('content-type', 'application/x-protobuf');

            foreach ($this->headers as $header => $value) {
                $request = $request->withHeader($header, $value);
            }

            if ($this->compression === 'gzip') {
                // TODO: Add Tests
                $body = $this->streamFactory->createStream(gzencode($bytes));
                $request = $request->withHeader('Content-Encoding', 'gzip');
            } else {
                $body = $this->streamFactory->createStream($bytes);
            }

            $request = $request->withBody($body);

            $response = $this->client->sendRequest($request);
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
    public function processHeaders($headers): array
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
     * validateEndpoint does two fuctions, firstly checks that the endpoint is valid
     *  secondly it appends https:// and /v1/traces should they have been omitted
     *
     * @param string $endpoint
     * @return string
     */
    private function validateEndpoint($endpoint)
    {
        $dsn = DsnParser::parseUrl($endpoint);

        if ($dsn->getScheme() === null) {
            $dsn = $dsn->withScheme('https');
        } elseif (!($dsn->getScheme() === 'https' || $dsn->getScheme() === 'http')) {
            throw new InvalidArgumentException('Expected scheme of http or https, given: ' . $dsn->getScheme());
        }

        if ($dsn->getPath() === null) {
            $dsn = $dsn->withPath('/v1/traces');
        }

        $dsn = $dsn->__toString();

        return $dsn;
    }

    /** @inheritDoc */
    public function shutdown(): bool
    {
        $this->running = false;

        return true;
    }

    /** @inheritDoc */
    public function forceFlush(): bool
    {
        return true;
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null)
    {
        $factory = new HttpFactory();
        $exporter = new Exporter(
            new Client(),
            $factory,
            $factory
        );

        return $exporter;
    }
}
