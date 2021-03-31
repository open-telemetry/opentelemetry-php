<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpGrpc;

use Opentelemetry\Proto\Common\V1\AnyValue;
use Opentelemetry\Proto\Common\V1\ArrayValue;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Trace\V1\Span as CollectorSpan;
use Opentelemetry\Proto\Trace\V1\Span\Event;
use Opentelemetry\Proto\Trace\V1\Span\SpanKind;
use Opentelemetry\Proto\Trace\V1\Status;
use Opentelemetry\Proto\Trace\V1\Status\StatusCode;
use OpenTelemetry\Trace\Span;

class SpanConverter
{

    public function as_otlp_key_value($key, $value): KeyValue
    {
        return new KeyValue([
            'key' => $key,
            'value' => $this->as_otlp_any_value($value),
        ]);
    }

    public function as_otlp_any_value($value): AnyValue
    {
        $result = new AnyValue();

        switch (true) {
            case is_array($value):

                $values = [];
                foreach ($value as $element) {
                    $this->as_otlp_any_value($element);
                }

                $result->setArrayValue(new ArrayValue($values));

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

    public function as_otlp_span(Span $span): CollectorSpan
    {
        $duration_ns = (($span->getEnd() - $span->getStart()));
        $end_timestamp = ($span->getStartEpochTimestamp() + $duration_ns);

        $row = [
            'trace_id' => hex2bin($span->getContext()->getTraceId()),
            'span_id' => hex2bin($span->getContext()->getSpanId()),
            'parent_span_id' => $span->getParent() ? hex2bin($span->getParent()->getSpanId()) : null,
            'name' => $span->getSpanName(),
            'start_time_unix_nano' => $span->getStartEpochTimestamp(),
            'end_time_unix_nano' => $end_timestamp,
            'kind' => $this->as_otlp_span_kind($span->getSpanKind()),
            // 'trace_state' => $span->getContext()
            // 'links' =>
        ];

        foreach ($span->getEvents() as $event) {
            if (!array_key_exists('events', $row)) {
                $row['events'] = [];
            }
            $row['events'][] = new Event([
                'time_unix_nano' => $event->getTimestamp(),
                'name' => $event->getName(),
            ]);
        }

        foreach ($span->getAttributes() as $k => $v) {
            if (!array_key_exists('attributes', $row)) {
                $row['attributes'] = [];
            }
            array_push($row['attributes'], $this->as_otlp_key_value($k, $v->getValue()));
        }

        if (!array_key_exists('status', $row)) {
            $proto_status = StatusCode::STATUS_CODE_OK;
            if ($span->getStatus()->getCanonicalStatusCode() === 'ERROR') {
                $proto_status = StatusCode::STATUS_CODE_ERROR;
            }
            $status=new Status();
            $row['status']=$status->setCode($proto_status)->setMessage('Description');
        }

        return new CollectorSpan($row);
    }
}
