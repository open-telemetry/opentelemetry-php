<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use function array_key_exists;
use function hex2bin;
use function iterator_to_array;
use OpenTelemetry\API\Trace as API;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use Opentelemetry\Proto\Common\V1\AnyValue;
use Opentelemetry\Proto\Common\V1\ArrayValue;
use Opentelemetry\Proto\Common\V1\InstrumentationScope;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Resource\V1\Resource as Resource_;
use Opentelemetry\Proto\Trace\V1\ResourceSpans;
use Opentelemetry\Proto\Trace\V1\ScopeSpans;
use Opentelemetry\Proto\Trace\V1\Span;
use Opentelemetry\Proto\Trace\V1\Span\Event;
use Opentelemetry\Proto\Trace\V1\Span\Link;
use Opentelemetry\Proto\Trace\V1\Span\SpanKind;
use Opentelemetry\Proto\Trace\V1\Status;
use Opentelemetry\Proto\Trace\V1\Status\StatusCode;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use function serialize;
use function spl_object_id;

class SpanConverter implements SpanConverterInterface
{
    public function convert(iterable $spans): array
    {
        $pExportTraceServiceRequest = new ExportTraceServiceRequest();

        $resourceSpans = [];
        $resourceCache = [];
        $scopeSpans = [];
        $scopeCache = [];
        foreach ($spans as $span) {
            $resource = $span->getResource();
            $instrumentationScope = $span->getInstrumentationScope();

            $resourceId = $resourceCache[spl_object_id($resource)] ??= serialize([
                $resource->getSchemaUrl(),
                $resource->getAttributes()->toArray(),
                $resource->getAttributes()->getDroppedAttributesCount(),
            ]);
            $instrumentationScopeId = $scopeCache[spl_object_id($instrumentationScope)] ??= serialize([
                $instrumentationScope->getName(),
                $instrumentationScope->getVersion(),
                $instrumentationScope->getSchemaUrl(),
                $instrumentationScope->getAttributes()->toArray(),
                $instrumentationScope->getAttributes()->getDroppedAttributesCount(),
            ]);

            if (!$pResourceSpans = $resourceSpans[$resourceId] ?? null) {
                $pExportTraceServiceRequest->getResourceSpans()[]
                    = $resourceSpans[$resourceId]
                    = $pResourceSpans
                    = $this->convertResourceSpans($resource);
            }

            if (!$pScopeSpans = $scopeSpans[$resourceId][$instrumentationScopeId] ?? null) {
                $pResourceSpans->getScopeSpans()[]
                    = $scopeSpans[$resourceId][$instrumentationScopeId]
                    = $pScopeSpans
                    = $this->convertScopeSpans($instrumentationScope);
            }

            $pScopeSpans->getSpans()[] = $this->as_otlp_span($span);
        }

        return iterator_to_array($pExportTraceServiceRequest->getResourceSpans());
    }

    private function convertResourceSpans(ResourceInfo $resource): ResourceSpans
    {
        $pResourceSpans = new ResourceSpans();
        $pResource = new Resource_();
        $this->setAttributes($pResource, $resource->getAttributes());
        $pResourceSpans->setResource($pResource);
        $pResourceSpans->setSchemaUrl((string) $resource->getSchemaUrl());

        return $pResourceSpans;
    }

    private function convertScopeSpans(InstrumentationScopeInterface $instrumentationScope): ScopeSpans
    {
        $pScopeSpans = new ScopeSpans();
        $pInstrumentationScope = new InstrumentationScope();
        $pInstrumentationScope->setName($instrumentationScope->getName());
        $pInstrumentationScope->setVersion((string) $instrumentationScope->getVersion());
        $pScopeSpans->setScope($pInstrumentationScope);
        $pScopeSpans->setSchemaUrl((string) $instrumentationScope->getSchemaUrl());

        return $pScopeSpans;
    }

    /**
     * @param Resource_|Span|Event|Link $pElement
     */
    private function setAttributes($pElement, AttributesInterface $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $pElement->getAttributes()[] = $this->as_otlp_key_value($key, $value);
        }
        $pElement->setDroppedAttributesCount($attributes->getDroppedAttributesCount());
    }

    private function as_otlp_key_value($key, $value): KeyValue
    {
        return new KeyValue([
            'key' => $key,
            'value' => $this->as_otlp_any_value($value),
        ]);
    }

    private function as_otlp_any_value($value): AnyValue
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

    private function as_otlp_span_kind($kind): int
    {
        switch ($kind) {
            case API\SpanKind::KIND_INTERNAL: return SpanKind::SPAN_KIND_INTERNAL;
            case API\SpanKind::KIND_CLIENT: return SpanKind::SPAN_KIND_CLIENT;
            case API\SpanKind::KIND_SERVER: return SpanKind::SPAN_KIND_SERVER;
            case API\SpanKind::KIND_PRODUCER: return SpanKind::SPAN_KIND_PRODUCER;
            case API\SpanKind::KIND_CONSUMER: return SpanKind::SPAN_KIND_CONSUMER;
        }

        return SpanKind::SPAN_KIND_UNSPECIFIED;
    }

    private function as_otlp_span(SpanDataInterface $span): Span
    {
        $parent_span = $span->getParentContext();
        $parent_span_id = $parent_span->isValid() ? $parent_span->getSpanId() : null;

        $row = [
            'trace_id' => hex2bin($span->getTraceId()),
            'span_id' => hex2bin($span->getSpanId()),
            'parent_span_id' => $parent_span_id ? hex2bin($parent_span_id) : null,
            'name' => $span->getName(),
            'start_time_unix_nano' => $span->getStartEpochNanos(),
            'end_time_unix_nano' => $span->getEndEpochNanos(),
            'kind' => $this->as_otlp_span_kind($span->getKind()),
            'trace_state' => (string) $span->getContext()->getTraceState(),
            'dropped_attributes_count' => $span->getAttributes()->getDroppedAttributesCount(),
            'dropped_events_count' => $span->getTotalDroppedEvents(),
            'dropped_links_count' => $span->getTotalDroppedLinks(),
        ];

        foreach ($span->getEvents() as $event) {
            if (!array_key_exists('events', $row)) {
                $row['events'] = [];
            }
            $attrs = [];

            foreach ($event->getAttributes() as $k => $v) {
                $attrs[] = $this->as_otlp_key_value($k, $v);
            }

            $row['events'][] = new Event([
                'time_unix_nano' => $event->getEpochNanos(),
                'name' => $event->getName(),
                'attributes' => $attrs,
                'dropped_attributes_count' => $event->getAttributes()->getDroppedAttributesCount(),
            ]);
        }

        foreach ($span->getLinks() as $link) {
            if (!array_key_exists('links', $row)) {
                $row['links'] = [];
            }
            $attrs = [];

            foreach ($link->getAttributes() as $k => $v) {
                $attrs[] = $this->as_otlp_key_value($k, $v);
            }

            $row['links'][] = new Link([
                'trace_id' => hex2bin($link->getSpanContext()->getTraceId()),
                'span_id' => hex2bin($link->getSpanContext()->getSpanId()),
                'trace_state' => (string) $link->getSpanContext()->getTraceState(),
                'attributes' => $attrs,
                'dropped_attributes_count' => $link->getAttributes()->getDroppedAttributesCount(),
            ]);
        }

        foreach ($span->getAttributes() as $k => $v) {
            if (!array_key_exists('attributes', $row)) {
                $row['attributes'] = [];
            }
            $row['attributes'][] = $this->as_otlp_key_value($k, $v);
        }

        $status = new Status();

        switch ($span->getStatus()->getCode()) {
            case API\StatusCode::STATUS_OK:
                $status->setCode(StatusCode::STATUS_CODE_OK);

                break;
            case API\StatusCode::STATUS_ERROR:
                $status->setCode(StatusCode::STATUS_CODE_ERROR)->setMessage($span->getStatus()->getDescription());

                break;
            default:
                $status->setCode(StatusCode::STATUS_CODE_UNSET);
        }

        $row['status'] = $status;

        return new Span(array_filter($row));
    }
}
