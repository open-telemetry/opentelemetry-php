<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\OtlpHttp;

use function array_key_exists;
use function hex2bin;
use Opentelemetry\Proto;
use Opentelemetry\Proto\Common\V1\AnyValue;
use Opentelemetry\Proto\Common\V1\ArrayValue;
use Opentelemetry\Proto\Common\V1\InstrumentationLibrary;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans;
use Opentelemetry\Proto\Trace\V1\ResourceSpans;
use Opentelemetry\Proto\Trace\V1\Span as CollectorSpan;
use Opentelemetry\Proto\Trace\V1\Span\Event;
use Opentelemetry\Proto\Trace\V1\Span\Link;
use Opentelemetry\Proto\Trace\V1\Span\SpanKind;
use Opentelemetry\Proto\Trace\V1\Status;
use Opentelemetry\Proto\Trace\V1\Status\StatusCode;
use OpenTelemetry\Sdk\Trace\ReadableSpan;
use OpenTelemetry\Sdk\Trace\SpanStatus;

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
                    $values[] = $this->as_otlp_any_value($element);
                }
                $result->setArrayValue(new ArrayValue(['values' => $values]));

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

    public function as_otlp_span(ReadableSpan $span): CollectorSpan
    {
        $end_timestamp = ($span->getStartEpochTimestamp() + $span->getDuration());

        $parent_span = $span->getParent();
        $parent_span_id = $parent_span ? $parent_span->getSpanId() : false;

        $row = [
            'trace_id' => hex2bin($span->getContext()->getTraceId()),
            'span_id' => hex2bin($span->getContext()->getSpanId()),
            'parent_span_id' => $parent_span_id ? hex2bin($parent_span_id) : null,
            'name' => $span->getSpanName(),
            'start_time_unix_nano' => $span->getStartEpochTimestamp(),
            'end_time_unix_nano' => $end_timestamp,
            'kind' => $this->as_otlp_span_kind($span->getSpanKind()),
            'trace_state' => (string) $span->getContext()->getTraceState(),
        ];

        foreach ($span->getEvents() as $event) {
            if (!array_key_exists('events', $row)) {
                $row['events'] = [];
            }
            $attrs = [];

            foreach ($event->getAttributes() as $k => $v) {
                $attrs[] = $this->as_otlp_key_value($k, $v->getValue());
            }

            $row['events'][] = new Event([
                'time_unix_nano' => $event->getTimestamp(),
                'name' => $event->getName(),
                'attributes' => $attrs,
            ]);
        }

        foreach ($span->getLinks() as $link) {
            if (!array_key_exists('links', $row)) {
                $row['links'] = [];
            }
            $attrs = [];

            foreach ($link->getAttributes() as $k => $v) {
                $attrs[] = $this->as_otlp_key_value($k, $v->getValue());
            }

            $row['links'][] = new Link([
                'trace_id' => hex2bin($link->getSpanContext()->getTraceId()),
                'span_id' => hex2bin($link->getSpanContext()->getSpanId()),
                'trace_state' => (string) $link->getSpanContext()->getTraceState(),
                'attributes' => $attrs,
            ]);
        }

        foreach ($span->getAttributes() as $k => $v) {
            if (!array_key_exists('attributes', $row)) {
                $row['attributes'] = [];
            }
            $row['attributes'][] = $this->as_otlp_key_value($k, $v->getValue());
        }

        $status = new Status();

        switch ($span->getStatus()->getCanonicalStatusCode()) {
            case SpanStatus::OK:
                $status->setCode(StatusCode::STATUS_CODE_OK);

                break;
            case SpanStatus::ERROR:
                $status->setCode(StatusCode::STATUS_CODE_ERROR)->setMessage($span->getStatus()->getStatusDescription());

                break;
            default:
                $status->setCode(StatusCode::STATUS_CODE_UNSET);
        }

        $row['status'] = $status;

        return new CollectorSpan($row);
    }

    // @return KeyValue[]
    public function as_otlp_resource_attributes(iterable $spans): array
    {
        $attrs = [];
        foreach ($spans as $span) {
            foreach ($span->getResource()->getAttributes() as $k => $v) {
                $attrs[] = $this->as_otlp_key_value($k, $v->getValue());
            }
        }

        return $attrs;
    }

    public function as_otlp_resource_span(iterable $spans): ResourceSpans
    {
        // TODO: Should return an empty ResourceSpans when $spans is empty
        // At the minute it returns an semi populated ResourceSpan

        $ils = $convertedSpans = [];
        foreach ($spans as $span) {
            /** @var \OpenTelemetry\Sdk\InstrumentationLibrary $il */
            $il = $span->getInstrumentationLibrary();
            $ilKey = sprintf('%s@%s', $il->getName(), $il->getVersion()??'');
            if (!isset($ils[$ilKey])) {
                $convertedSpans[$ilKey] = [];
                $ils[$ilKey] = new InstrumentationLibrary(['name' => $il->getName(), 'version' => $il->getVersion()??'']);
            }
            $convertedSpans[$ilKey][] = $this->as_otlp_span($span);
        }

        $ilSpans = [];
        foreach ($ils as $ilKey => $il) {
            $ilSpans[] = new InstrumentationLibrarySpans([
                'instrumentation_library' => $il,
                'spans' => $convertedSpans[$ilKey],
            ]);
        }

        return new Proto\Trace\V1\ResourceSpans([
            'resource' => new Proto\Resource\V1\Resource([
                'attributes' => $this->as_otlp_resource_attributes($spans),
            ]),
            'instrumentation_library_spans' => $ilSpans,
        ]);
    }
}
