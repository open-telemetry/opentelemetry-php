<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Trace as API;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
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
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use function serialize;
use function spl_object_id;

final class SpanConverter
{
    private ProtobufSerializer $serializer;

    public function __construct(?ProtobufSerializer $serializer = null)
    {
        $this->serializer = $serializer ?? ProtobufSerializer::getDefault();
    }

    public function convert(iterable $spans): ExportTraceServiceRequest
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

            if (($pResourceSpans = $resourceSpans[$resourceId] ?? null) === null) {
                /** @psalm-suppress InvalidArgument */
                $pExportTraceServiceRequest->getResourceSpans()[]
                    = $resourceSpans[$resourceId]
                    = $pResourceSpans
                    = $this->convertResourceSpans($resource);
            }

            if (($pScopeSpans = $scopeSpans[$resourceId][$instrumentationScopeId] ?? null) === null) {
                /** @psalm-suppress InvalidArgument */
                $pResourceSpans->getScopeSpans()[]
                    = $scopeSpans[$resourceId][$instrumentationScopeId]
                    = $pScopeSpans
                    = $this->convertScopeSpans($instrumentationScope);
            }

            /** @psalm-suppress InvalidArgument */
            $pScopeSpans->getSpans()[] = $this->convertSpan($span);
        }

        return $pExportTraceServiceRequest;
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
        $this->setAttributes($pInstrumentationScope, $instrumentationScope->getAttributes());
        $pScopeSpans->setScope($pInstrumentationScope);
        $pScopeSpans->setSchemaUrl((string) $instrumentationScope->getSchemaUrl());

        return $pScopeSpans;
    }

    /**
     * @param Resource_|Span|Event|Link|InstrumentationScope $pElement
     */
    private function setAttributes($pElement, AttributesInterface $attributes): void
    {
        foreach ($attributes as $key => $value) {
            /** @psalm-suppress InvalidArgument */
            $pElement->getAttributes()[] = (new KeyValue())
                ->setKey($key)
                ->setValue(AttributesConverter::convertAnyValue($value));
        }
        $pElement->setDroppedAttributesCount($attributes->getDroppedAttributesCount());
    }

    private function convertSpanKind(int $kind): int
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

    private function convertStatusCode(string $status): int
    {
        switch ($status) {
            case API\StatusCode::STATUS_UNSET: return StatusCode::STATUS_CODE_UNSET;
            case API\StatusCode::STATUS_OK: return StatusCode::STATUS_CODE_OK;
            case API\StatusCode::STATUS_ERROR: return StatusCode::STATUS_CODE_ERROR;
        }

        return StatusCode::STATUS_CODE_UNSET;
    }

    private function convertSpan(SpanDataInterface $span): Span
    {
        $pSpan = new Span();
        $pSpan->setTraceId($this->serializer->serializeTraceId($span->getContext()->getTraceIdBinary()));
        $pSpan->setSpanId($this->serializer->serializeSpanId($span->getContext()->getSpanIdBinary()));
        $pSpan->setFlags($span->getContext()->getTraceFlags());
        $pSpan->setTraceState((string) $span->getContext()->getTraceState());
        if ($span->getParentContext()->isValid()) {
            $pSpan->setParentSpanId($this->serializer->serializeSpanId($span->getParentContext()->getSpanIdBinary()));
        }
        $pSpan->setName($span->getName());
        $pSpan->setKind($this->convertSpanKind($span->getKind()));
        $pSpan->setStartTimeUnixNano($span->getStartEpochNanos());
        $pSpan->setEndTimeUnixNano($span->getEndEpochNanos());
        $this->setAttributes($pSpan, $span->getAttributes());

        foreach ($span->getEvents() as $event) {
            /** @psalm-suppress InvalidArgument */
            $pSpan->getEvents()[] = $pEvent = new Event();
            $pEvent->setTimeUnixNano($event->getEpochNanos());
            $pEvent->setName($event->getName());
            $this->setAttributes($pEvent, $event->getAttributes());
        }
        $pSpan->setDroppedEventsCount($span->getTotalDroppedEvents());

        foreach ($span->getLinks() as $link) {
            /** @psalm-suppress InvalidArgument */
            $pSpan->getLinks()[] = $pLink = new Link();
            $pLink->setTraceId($this->serializer->serializeTraceId($link->getSpanContext()->getTraceIdBinary()));
            $pLink->setSpanId($this->serializer->serializeSpanId($link->getSpanContext()->getSpanIdBinary()));
            $pLink->setFlags($link->getSpanContext()->getTraceFlags());
            $pLink->setTraceState((string) $link->getSpanContext()->getTraceState());
            $this->setAttributes($pLink, $link->getAttributes());
        }
        $pSpan->setDroppedLinksCount($span->getTotalDroppedLinks());

        $pStatus = new Status();
        $pStatus->setMessage($span->getStatus()->getDescription());
        $pStatus->setCode($this->convertStatusCode($span->getStatus()->getCode()));
        $pSpan->setStatus($pStatus);

        return $pSpan;
    }
}
