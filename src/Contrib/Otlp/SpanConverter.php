<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\SpanContextInterface;
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
use Opentelemetry\Proto\Trace\V1\SpanFlags;
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
    private readonly ProtobufSerializer $serializer;

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
        return match ($kind) {
            API\SpanKind::KIND_INTERNAL => SpanKind::SPAN_KIND_INTERNAL,
            API\SpanKind::KIND_CLIENT => SpanKind::SPAN_KIND_CLIENT,
            API\SpanKind::KIND_SERVER => SpanKind::SPAN_KIND_SERVER,
            API\SpanKind::KIND_PRODUCER => SpanKind::SPAN_KIND_PRODUCER,
            API\SpanKind::KIND_CONSUMER => SpanKind::SPAN_KIND_CONSUMER,
            default => SpanKind::SPAN_KIND_UNSPECIFIED,
        };
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
        $pSpan->setFlags(self::buildFlagsForSpan($span->getContext(), parentSpanContext: $span->getParentContext()));
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
            $pLink->setFlags(self::buildFlagsForLink($link->getSpanContext()));
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

    private static function addRemoteFlags(SpanContextInterface $spanContext, int $baseFlags): int
    {
        $flags = $baseFlags;
        $flags |= SpanFlags::SPAN_FLAGS_CONTEXT_HAS_IS_REMOTE_MASK;
        if ($spanContext->isRemote()) {
            $flags |= SpanFlags::SPAN_FLAGS_CONTEXT_IS_REMOTE_MASK;
        }

        return $flags;
    }

    private static function buildFlagsForSpan(SpanContextInterface $spanContext, SpanContextInterface $parentSpanContext): int
    {
        $flags = $spanContext->getTraceFlags();

        /**
         * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.5.0/opentelemetry/proto/trace/v1/trace.proto#L122
         *
         * Bits 8 and 9 represent the 3 states of whether a span's parent is remote.
         *                                                         ^^^^^^
         * That is why we pass parent span's context.
         */
        $flags = self::addRemoteFlags($parentSpanContext, $flags);

        return $flags;
    }

    private static function buildFlagsForLink(SpanContextInterface $linkSpanContext): int
    {
        $flags = $linkSpanContext->getTraceFlags();

        /**
         * @see https://github.com/open-telemetry/opentelemetry-proto/blob/v1.5.0/opentelemetry/proto/trace/v1/trace.proto#L279
         *
         * Bits 8 and 9 represent the 3 states of whether the link is remote.
         *                                                    ^^^^
         * That is why we pass link span's context.
         */
        $flags = self::addRemoteFlags($linkSpanContext, $flags);

        return $flags;
    }
}
