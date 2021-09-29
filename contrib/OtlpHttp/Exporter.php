<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use OpenTelemetry\Sdk\Trace;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Exporter implements Trace\Exporter
{
    /**
     * @var string
     */
    private $endpointUrl;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $insecure;

    /**
     * @var string
     */
    private $certificateFile;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $compression;

    /**
     * @var int
     */
    private $timeout;
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
        $this->endpointUrl = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'https://localhost:55681/v1/traces';
        $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'http/protobuf';
        $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: 'none';
        $this->headers = $this->processHeaders(getenv('OTEL_EXPORTER_OTLP_HEADERS'));
        $this->compression = getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: 'none';
        $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: 10;

        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->spanConverter =  $spanConverter ?? new SpanConverter();

        if ($this->protocol != 'http/protobuf') {
            throw new InvalidArgumentException('Invalid OTLP Protocol Specified');
        }

        $parsedDsn = parse_url($this->endpointUrl);

        if (!is_array($parsedDsn)) {
            throw new InvalidArgumentException('Unable to parse provided DSN');
        }

        if (
            !isset($parsedDsn['scheme'])
            || !isset($parsedDsn['host'])
            || !isset($parsedDsn['port'])
            || !isset($parsedDsn['path'])
        ) {
            throw new InvalidArgumentException('Endpoint should have scheme, host, port and path');
        }
    }

    /** @inheritDoc */
    public function export(iterable $spans): int
    {
        if (!$this->running) {
            return Trace\Exporter::FAILED_NOT_RETRYABLE;
        }

        if (empty($spans)) {
            return Trace\Exporter::SUCCESS;
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
            return Trace\Exporter::FAILED_NOT_RETRYABLE;
        } catch (NetworkExceptionInterface | ClientExceptionInterface $e) {
            return Trace\Exporter::FAILED_RETRYABLE;
        }

        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            return Trace\Exporter::FAILED_NOT_RETRYABLE;
        }

        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            return Trace\Exporter::FAILED_RETRYABLE;
        }

        return Trace\Exporter::SUCCESS;
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

            list($key, $value) = $kv;

            $metadata[$key] = $value;
        }

        return $metadata;
    }

    public function shutdown(): void
    {
        $this->running = false;
    }

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
