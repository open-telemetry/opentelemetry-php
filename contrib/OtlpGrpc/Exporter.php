<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use grpc;
use InvalidArgumentException;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\Sdk\Trace;
use OpenTelemetry\Trace as API;

class Exporter implements Trace\Exporter
{
    /**
     * @var string
     */
    private $endpointURL;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var bool|string
     */
    private $insecure;

    /**
     * @var string
     */
    private $certificateFile;

    /**
     * @var string
     */
    private $headers;

    /**
     * @var bool|string
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

    private $metadata;
    
    /**
    * @var bool
    */
    private $running = true;

    /**
     * @var TraceServiceClient
     */

    private $client;

    /**
     * OTLP GRPC Exporter Constructor
     */
    public function __construct(
        string $endpointURL = 'localhost:4317',
        bool $insecure = true,
        string $certificateFile = '',
        string $headers = '',
        bool $compression = false,
        int $timeout = 10,
        TraceServiceClient $client = null
    ) {

        // Set default values based on presence of env variable
        $this->endpointURL = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: $endpointURL;
        $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'grpc'; // I guess this is redundant?
        $this->insecure = getenv('OTEL_EXPORTER_OTLP_INSECURE') ?: $insecure;
        $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: $certificateFile;
        $this->headers = getenv('OTEL_EXPORTER_OTLP_HEADERS') ?: $headers;
        $this->compression = getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: $compression;
        $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: $timeout;

        $this->spanConverter = new SpanConverter();

        $this->metadata = $this->metadataFromHeaders($this->headers);

        $opts = [
            'update_metadata' => function () {
                return $this->metadata;
            },
            'timeout' => $this->timeout,
        ];

        if (!$this->insecure && !$this->certificateFile) {
            // Assumed default
            $opts['credentials'] = Grpc\ChannelCredentials::createSsl('');
        } elseif (!$this->insecure && $this->certificateFile !== '') {
            // Should we validate more?
            $opts['credentials'] = Grpc\ChannelCredentials::createSsl(file_get_contents($certificateFile));
        } else {
            $opts['credentials'] = Grpc\ChannelCredentials::createInsecure();
        }

        if ($this->compression) {
            // gzip is the only specified compression method for now
            $opts['grpc.default_compression_algorithm'] = 2;
        }

        $this->client = $client ?? new TraceServiceClient($this->endpointURL, $opts);
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

        $resourcespans = [$this->spanConverter->as_otlp_resource_span($spans)];

        $request = new ExportTraceServiceRequest([
            'resource_spans' => $resourcespans,
        ]);

        list($response, $status) = $this->client->Export($request)->wait();

        if ($status->code == \Grpc\STATUS_OK) {
            return Trace\Exporter::SUCCESS;
        }

        if (in_array($status->code, [
            \Grpc\STATUS_CANCELLED,
            \Grpc\STATUS_DEADLINE_EXCEEDED,
            \Grpc\STATUS_PERMISSION_DENIED,
            \Grpc\STATUS_RESOURCE_EXHAUSTED,
            \Grpc\STATUS_ABORTED,
            \Grpc\STATUS_OUT_OF_RANGE,
            \Grpc\STATUS_UNAVAILABLE,
            \Grpc\STATUS_DATA_LOSS,
            \Grpc\STATUS_UNAUTHENTICATED,
        ])) {
            return Trace\Exporter::FAILED_RETRYABLE;
        }

        return Trace\Exporter::FAILED_NOT_RETRYABLE;
    }

    public function setHeader($key, $value)
    {
        $this->metadata[$key] = [$value];
    }

    public function getHeaders()
    {
        return $this->metadata;
    }

    public function metadataFromHeaders($headers): array
    {
        if (is_array($headers)) {
            throw new InvalidArgumentException('Configuring Headers Via');
        }

        $pairs = explode(',', $headers);

        if (!array_key_exists(1, $pairs)) {
            return [];
        }

        $metadata = [];
        foreach ($pairs as $pair) {
            list($key, $value) = explode('=', $pair, 2);
            $metadata[$key] = [$value];
        }

        return $metadata;
    }

    public function shutdown(): void
    {
        $this->running = false;
    }

    // public function getHeaders()
    // {
    //     return $this->metadataFromHeaders($this->headers);
    // }
}
