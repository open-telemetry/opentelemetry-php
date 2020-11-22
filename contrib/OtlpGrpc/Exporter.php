<?php

declare(strict_types=1);
namespace OpenTelemetry\Contrib\OtlpGrpc;
require __DIR__ . '/../../vendor/autoload.php';


use grpc;
use OpenTelemetry\Sdk\Trace;
use OpenTelemetry\Trace as API;
use Opentelemetry\Proto\Collector\Trace\V1;

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
     * Exporter constructor.
     * @param string $serviceName
     */
    public function __construct(
        $serviceName,
        ClientInterface $client=null
    ) {

        // Set default values based on presence of env variable
        $this->endpointURL = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'localhost:55680';
        $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'grpc';
        $this->insecure = getenv('OTEL_EXPORTER_OTLP_INSECURE') ?: 'false';
        $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: 'none';
        $this->headers[] = getenv('OTEL_EXPORTER_OTLP_HEADERS') ?: 'none';
        $this->compression = getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: 'none';
        $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: 10;

        $this->client = $client ?? new V1\TraceServiceClient($endpointURL, [
        'credentials' => Grpc\ChannelCredentials::createInsecure(),
    ]);
        $this->spanConverter = new SpanConverter($serviceName);
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
            array_push($convertedSpans, $this->spanConverter->convert($span));
        }

        $request= new V1\ExportTraceServiceRequest();
        $request->setResourceSpans($convertedSpans);

        list($response, $status) = $client->Export($request)->wait();
        if ($status->code !== Grpc\STATUS_OK) {
            echo "ERROR: " . $status->code . ", " . $status->details . PHP_EOL;
            exit(1);
            }
        echo $response->getMessage() . PHP_EOL;
        
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
