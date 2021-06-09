<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use OpenTelemetry\Sdk\Trace;
use OpenTelemetry\Trace as API;
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
        $this->endpointUrl = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'localhost:55681/v1/traces';
        $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'http/protobuf';
        $this->insecure = getenv('OTEL_EXPORTER_OTLP_INSECURE') ?: 'false';
        $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: 'none';
        $this->headers[] = getenv('OTEL_EXPORTER_OTLP_HEADERS') ?: 'none';
        $this->compression = getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: 'none';
        $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: 10;

        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->spanConverter =  $spanConverter ?? new SpanConverter();
    }

    /**
     * Exports the provided Span data via the OTLP protocol
     *
     * @param iterable<API\Span> $spans Array of Spans
     * @return int return code, defined on the Exporter interface
     */
    public function export(iterable $spans): int
    {
        if (!$this->running) {
            return Exporter::FAILED_NOT_RETRYABLE;
        }

        if (empty($spans)) {
            return Trace\Exporter::SUCCESS;
        }

        if ($this->protocol === 'http/json') {
            // https://github.com/open-telemetry/opentelemetry-specification/issues/786
            return Exporter::FAILED_NOT_RETRYABLE;
        }

        $resourcespans = [$this->spanConverter->as_otlp_resource_span($spans)];

        $exportrequest = new ExportTraceServiceRequest([
            'resource_spans' => $resourcespans,
        ]);

        $proto = $exportrequest->serializeToString();

        try {
            $body = $this->streamFactory->createStream($proto);

            $request = $this->requestFactory
                ->createRequest('POST', $this->endpointUrl)
                ->withHeader('content-type', 'application/x-protobuf')
                ->withBody($body);

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

    public function shutdown(): void
    {
        $this->running = false;
    }
}
