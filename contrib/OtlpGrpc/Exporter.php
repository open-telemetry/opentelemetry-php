<?php

declare(strict_types=1);
namespace OpenTelemetry\Contrib\OtlpGrpc;


use grpc;
use OpenTelemetry\Sdk\Trace;
use OpenTelemetry\Trace as API;
use OpenTelemetry\Trace\Span;
use Opentelemetry\Proto;
use Opentelemetry\Proto\Collector\Trace\V1;
use Opentelemetry\Proto\Common\V1\InstrumentationLibrary;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Common\V1\AnyValue;
use Opentelemetry\Proto\Trace\V1\Span as CollectorSpan;
use Opentelemetry\Proto\Trace\V1\Span\SpanKind;
use Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans;
use Opentelemetry\Proto\Trace\V1\Status\StatusCode;
use Opentelemetry\Proto\Trace\V1\Status;

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
        $serviceName,
        ClientInterface $client=null
    ) {

        // Set default values based on presence of env variable
        $this->endpointURL = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'host.docker.internal:4317';
        //$this->endpointURL = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'api.honeycomb.io:443';
        $this->protocol = getenv('OTEL_EXPORTER_OTLP_PROTOCOL') ?: 'grpc';
        $this->insecure = getenv('OTEL_EXPORTER_OTLP_INSECURE') ?: 'false';
        $this->certificateFile = getenv('OTEL_EXPORTER_OTLP_CERTIFICATE') ?: 'none';
        $this->headers[] = getenv('OTEL_EXPORTER_OTLP_HEADERS') ?: 'none';
        $this->compression = getenv('OTEL_EXPORTER_OTLP_COMPRESSION') ?: 'none';
        $this->timeout =(int) getenv('OTEL_EXPORTER_OTLP_TIMEOUT') ?: 10;


        $opts = [
            'credentials' => Grpc\ChannelCredentials::createInsecure(),
            //'credentials' => Grpc\ChannelCredentials::createSsl(),
            'update_metadata' => function() {
                return [
                    'x-honeycomb-team' => ['xxx'],
                    'x-honeycomb-dataset' =>  ['xxx'],
                ];
            },
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
            array_push($convertedSpans, $this->as_otlp_span($span));
        }


        $il = new InstrumentationLibrary([
            'name' => 'otel-php',
            'version' => '0.0.1'
        ]);

        $ilspans = [];
        foreach($convertedSpans as $convertedSpan) {
            $ilspan = new InstrumentationLibrarySpans([
                'instrumentation_library' => $il,
                'spans' => [$convertedSpan]
            ]);

            array_push($ilspans, $ilspan);
        }


        $resourcespans = new Proto\Trace\V1\ResourceSpans([
            'instrumentation_library_spans' => $ilspans
        ]);

        
        $request= new V1\ExportTraceServiceRequest();
        $request->setResourceSpans([$resourcespans]);


        list($response, $status) = $this->client->Export($request)->wait();
        if ($status->code !== Grpc\STATUS_OK) {
            echo "ERROR: " . $status->code . ", " . $status->details . PHP_EOL;
        }


        // TODO: Make this work
        //echo $response->getMessage() . PHP_EOL;

        // if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
        //     return Trace\Exporter::FAILED_NOT_RETRYABLE;
        // }

        // if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
        //     return Trace\Exporter::FAILED_RETRYABLE;
        // }

        return Trace\Exporter::SUCCESS;
    }

    public function as_otlp_key_value($key, $value): KeyValue {
        return new KeyValue([
            'key' => $key,
            'value' => $this->as_otlp_any_value($value)
        ]);
    }

    public function as_otlp_any_value($value): AnyValue
    {
        $result = new AnyValue();

        switch (true) {
            case is_array($value):
                $result->setArrayValue($value);
                break;
            case is_int($value):
                $result->setIntValue($value);
                break;
            case is_bool($value):
                $result->setBoolValue($value);
                break;
            case is_double($value):
                $result->setDoubleValue($value);
                break;
            case is_string($value):
                $result->setStringValue($value);
                break;
        }

        return $result;
    }

    public function as_otlp_span_kind($kind): int
    {

        switch ($kind) {
            case 0: return SpanKind::SPAN_KIND_INTERNAL;
            case 1: return SpanKind::SPAN_KIND_CLIENT;
            case 2: return SpanKind::SPAN_KIND_SERVER;
            case 3: return SpanKind::SPAN_KIND_PRODUCER;
            case 4: return SpanKind::SPAN_KIND_CONSUMER;
        }

        return SpanKind::SPAN_KIND_UNSPECIFIED;
    }

    // ["trace_id":protected]=> string(32) "f8ebf54644f65fae5e98c5dd84b9d196"
    // ["span_id":protected]=> string(16) "b90a8323cfd7cd66"
    public function as_otlp_span(Span $span): CollectorSpan
    {

        $duration_ns = (($span->getEnd() - $span->getStart()));
        $end_timestamp = ($span->getStartEpochTimestamp() + $duration_ns);

        $row = [
            // TODO: Fix this, I was getting 32, and 16 byte ids sent to the collector which it didn't like
            // fudged it with this substr for now, it's possibly an easy fix
            'trace_id' => substr($span->getContext()->getTraceId(), 0, 16),
            'span_id' => substr($span->getContext()->getSpanId(), 0, 8),
            'parent_span_id' => $span->getParent() ? $span->getParent()->getSpanId() : null,
            // 'localEndpoint' => [
            //     'serviceName' => $this->serviceName,
            // ],
            'name' => $span->getSpanName(),
            'start_time_unix_nano' => $span->getStartEpochTimestamp(),
            'end_time_unix_nano' => $end_timestamp,
            'kind' => $this->as_otlp_span_kind($span->getSpanKind()),
            // 'trace_state' => $span->getContext()
            // 'events' =>
            // 'links' =>
        ];


        foreach ($span->getEvents() as $event) {
            if (!array_key_exists('annotations', $row)) {
                $row['annotations'] = [];
            }
            $row['annotations'][] = [
                'timestamp' => (int) ($event->getTimestamp() / 1e3), // RealtimeClock in microseconds
                'value' => $event->getName(),
            ];
        }

        foreach ($span->getAttributes() as $k => $v) {
            if (!array_key_exists('attributes', $row)) {
                $row['attributes'] = [];
            }
            array_push($row['attributes'], $this->as_otlp_key_value($k, $v->getValue()));

        }

        if (!array_key_exists('status', $row)) {
            $proto_status = StatusCode::STATUS_CODE_OK;
            if ($span->getStatus()->getCanonicalStatusCode() === "ERROR") {
                $proto_status = StatusCode::STATUS_CODE_ERROR;
            }
            $status=new Status();
            $row['status']=$status->setCode($proto_status)->setMessage("Description");
        }

        return new CollectorSpan($row);

    }
    public function shutdown(): void
    {
        $this->running = false;
    }

}
