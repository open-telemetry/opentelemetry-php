<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use grpc;
use Opentelemetry\Proto;
use Opentelemetry\Proto\Collector\Trace\V1;
use Opentelemetry\Proto\Common\V1\InstrumentationLibrary;
use Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans;
use OpenTelemetry\Sdk\Trace;
use OpenTelemetry\Trace as API;

use OpenTelemetry\Trace\Span;

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
     * OTLP GRPC Exporter Constructor
     * @param string $serviceName
     */
    public function __construct(
        string $endpointURL = 'localhost:4317',
        bool $insecure = true,
        string $certificateFile = null,
        array $headers = [],
        bool $compression = false,
        int $timeout = 10,
        ClientInterface $client = null
    ) {

        // Set default values based on presence of env variable
        $this->endpointURL = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: $endpointURL;
        $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'grpc'; // I guess this is redundant?
        $this->insecure = getenv('OTEL_EXPORTER_OTLP_INSECURE') ?: $insecure;
        $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: $certificateFile;
        $this->headers[] = getenv('OTEL_EXPORTER_OTLP_HEADERS') ?: $headers; // TODO
        $this->compression = getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: $compression; // TODO
        $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: $timeout; // TODO

        $this->spanConverter = new SpanConverter('foo');

        if (!$this->insecure && !$this->certificateFile) {
            // Assumed default
            $_credentials = Grpc\ChannelCredentials::createSsl();
        } elseif (!$this->insecure && $this->certificateFile) {
            // Should we validate more?
            $_credentials = Grpc\ChannelCredentials::createSsl(file_get_contents($certificateFile));
        } else {
            $_credentials = Grpc\ChannelCredentials::createInsecure();
        }

        $opts = [
            'credentials' => $_credentials,
            'update_metadata' =>  function () {
                return [
                    'x-honeycomb-team' => ['xxx'],
                    'x-honeycomb-dataset' => ['xxx'],
                ];
            },
            // https://github.com/grpc/grpc/tree/master/src/php#compression
            // 'grpc.default_compression_algorithm' => 2,
            // 'grpc.default_compression_level' => 2,
        ];

        $this->client = $client ?? new V1\TraceServiceClient($this->endpointURL, $opts);
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

        $convertedSpans = [];
        foreach ($spans as $span) {
            array_push($convertedSpans, $this->spanConverter->as_otlp_span($span));
        }

        $il = new InstrumentationLibrary([
            'name' => 'otel-php',
            'version' => '0.0.1',
        ]);

        $ilspans = [];
        foreach ($convertedSpans as $convertedSpan) {
            $ilspan = new InstrumentationLibrarySpans([
                'instrumentation_library' => $il,
                'spans' => [$convertedSpan],
            ]);

            array_push($ilspans, $ilspan);
        }

        $resourcespans = new Proto\Trace\V1\ResourceSpans([
            'instrumentation_library_spans' => $ilspans,
        ]);

        $request= new V1\ExportTraceServiceRequest();
        $request->setResourceSpans([$resourcespans]);

        list($response, $status) = $this->client->Export($request)->wait();
        if ($status->code !== Grpc\STATUS_OK) {
            // TODO: This probably shouldn't  echo
            echo 'ERROR: ' . $status->code . ', ' . $status->details . PHP_EOL;
        }

        if ($status->code >= 400 && $status->code < 500) {
            return Trace\Exporter::FAILED_NOT_RETRYABLE;
        }

        if ($status->code >= 500 && $status->code < 600) {
            return Trace\Exporter::FAILED_RETRYABLE;
        }

        return Trace\Exporter::SUCCESS;
    }

    public function shutdown(): void
    {
        $this->running = false;
    }
}
