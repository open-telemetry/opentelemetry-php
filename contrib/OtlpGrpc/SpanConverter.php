<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use OpenTelemetry\Trace\Span;
use Opentelemetry\Proto\Trace\V1\Span as CollectorSpan;
use Opentelemetry\Proto\Trace\V1\Status\StatusCode;
use Opentelemetry\Proto\Trace\V1\Status;
use Opentelemetry\Proto\Common\V1\InstrumentationLibrary

class SpanConverter
{
    /**
     * @var string
     */
    private $serviceName;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    private function sanitiseTagValue($value)
    {
        // Casting false to string makes an empty string
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // OTLP tags must be strings, but opentelemetry
        // accepts strings, booleans, numbers, and lists of each.
        if (is_array($value)) {
            return join(',', array_map([$this, 'sanitiseTagValue'], $value));
        }

        // Floats will lose precision if their string representation
        // is >=14 or >=17 digits, depending on PHP settings.
        // Can also throw E_RECOVERABLE_ERROR if $value is an object
        // without a __toString() method.
        // This is possible because OpenTelemetry\Trace\Span does not verify
        // setAttribute() $value input.
        return (string) $value;
    }

    public function convert(Span $span)
    {   
        $instrumentation_library_spans=[];
        $il=new InstrumentationLibrary();
        $name=$il->getName();
        $version=$il->getVersion();



        $spanParent = $span->getParent();
        $row = [
            'id' => $span->getContext()->getSpanId(),
            'traceId' => $span->getContext()->getTraceId(),
            'parentId' => $spanParent ? $spanParent->getSpanId() : null,
            'localEndpoint' => [
                'serviceName' => $this->serviceName,
            ],
            'name' => $span->getSpanName(),
            'timestamp' => (int) ($span->getStartEpochTimestamp() / 1e3), // RealtimeClock in microseconds
            'duration' => (int) (($span->getEnd() - $span->getStart()) / 1e3), // Diff in microseconds
            'trace_state' => $span->getContext();

        ];

        foreach ($span->getAttributes() as $k => $v) {
            if (!array_key_exists('tags', $row)) {
                $row['tags'] = [];
            }
            $row['tags'][$k] = $this->sanitiseTagValue($v->getValue());
        }

        foreach ($span->getEvents() as $event) {
            if (!array_key_exists('annotations', $row)) {
                $row['annotations'] = [];
            }
            $row['annotations'][] = [
                'timestamp' => (int) ($event->getTimestamp() / 1e3), // RealtimeClock in microseconds
                'value' => $event->getName(),
            ];
        }

        foreach ($span->getLinks() as $link) {
            if (!array_key_exists('link', $row)) {
                $row['link'] = [];
            }
            $row['link'][] = [
                'trace_id' => $span->getContext()->getTraceId(),
                'span_id' => $span->getContext()->getSpanId(),
            ];
        }
        if (!array_key_exists('status', $row)) {
                $proto_status = StatusCode::STATUS_CODE_OK;
                if ($span->getStatus()->getCanonicalStatusCode() === "ERROR") {
                    $proto_status = StatusCode::STATUS_CODE_ERROR;
                }
                $status=new Status();
                $row['status']=$status->setCode('$proto_status')->setMessage("Description");
            }

        return $row;
    }
}
            // $key=new keyValue();
            // $key->setKey("id")
            // $value=new AnyValue();
            // $value->setStringValue($span->getContext()->getSpanId());
            // $key->setValue($value);
