<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Jaeger\Thrift\Log;
use Jaeger\Thrift\Span as JTSpan;
use Jaeger\Thrift\SpanRef;
use Jaeger\Thrift\SpanRefType;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Jaeger\TagFactory\TagFactory;
use OpenTelemetry\SDK\Common\Time\Util as TimeUtil;
use OpenTelemetry\SDK\Trace\EventInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use RuntimeException;

class SpanConverter implements SpanConverterInterface
{
    const STATUS_CODE_TAG_KEY = 'otel.status_code';
    const STATUS_OK = 'OK';
    const STATUS_ERROR = 'ERROR';
    const KEY_ERROR_FLAG = 'error';
    const STATUS_DESCRIPTION_TAG_KEY = 'otel.status_description';
    const KEY_INSTRUMENTATION_SCOPE_NAME = 'otel.scope.name';
    const KEY_INSTRUMENTATION_SCOPE_VERSION = 'otel.scope.version';
    const KEY_SPAN_KIND = 'span.kind';
    const JAEGER_SPAN_KIND_CLIENT = 'client';
    const JAEGER_SPAN_KIND_SERVER = 'server';
    const JAEGER_SPAN_KIND_CONSUMER = 'consumer';
    const JAEGER_SPAN_KIND_PRODUCER = 'producer';
    const EVENT_ATTRIBUTE_KEY_NAMED_EVENT = 'event';
    const JAEGER_KEY_EVENT = 'event';

    public function __construct()
    {
        self::checkIfPHPSupports64BitIntegers();
    }

    private static function checkIfPHPSupports64BitIntegers(): void
    {
        if (PHP_INT_SIZE < 8) {
            $humanReadableIntSize = PHP_INT_SIZE*8;

            throw new RuntimeException("Integrating with Jaeger requires usage of 64 bit integers, but your current platform is $humanReadableIntSize bit. See https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/sdk_exporters/jaeger.md#ids for more information.");
        }
    }

    public function convert(iterable $spans): array
    {
        $aggregate = [];
        foreach ($spans as $span) {
            $aggregate[] = $this->convertSpan($span);
        }

        return $aggregate;
    }

    private function convertSpan(SpanDataInterface $span): JTSpan
    {
        [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
            'spanId' => $spanId,
            'parentSpanId' => $parentSpanId,
        ] = self::convertOtelToJaegerIds($span);

        $startTime = TimeUtil::nanosToMicros($span->getStartEpochNanos());
        $duration = TimeUtil::nanosToMicros($span->getEndEpochNanos() - $span->getStartEpochNanos());

        $tags = self::convertOtelSpanDataToJaegerTags($span);

        $logs = self::convertOtelEventsToJaegerLogs($span);

        $references = self::convertOtelLinksToJaegerSpanReferences($span);

        return new JTSpan([
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
            'spanId' => $spanId,
            'parentSpanId' => $parentSpanId,
            'operationName' => $span->getName(),
            'flags' => $span->getContext()->getTraceFlags(),
            'startTime' => $startTime,
            'duration' => ($duration < 0) ? 0 : $duration,
            'tags' => $tags,
            'logs' => $logs,
            'references' => $references,
        ]);
    }

    private static function convertOtelToJaegerIds(SpanDataInterface $span): array
    {
        [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh
        ] = IdConverter::convertOtelToJaegerTraceIds($span->getContext()->getTraceID());

        $spanId = IdConverter::convertOtelToJaegerSpanId($span->getContext()->getSpanID());
        $parentSpanId = IdConverter::convertOtelToJaegerSpanId($span->getParentSpanId());

        return [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
            'spanId' => $spanId,
            'parentSpanId' => $parentSpanId,
        ];
    }

    private static function convertOtelSpanKindToJaeger(SpanDataInterface $span): ?string
    {
        switch ($span->getKind()) {
            case SpanKind::KIND_CLIENT:
                return self::JAEGER_SPAN_KIND_CLIENT;
            case SpanKind::KIND_SERVER:
                return self::JAEGER_SPAN_KIND_SERVER;
            case SpanKind::KIND_CONSUMER:
                return self::JAEGER_SPAN_KIND_CONSUMER;
            case SpanKind::KIND_PRODUCER:
                return self::JAEGER_SPAN_KIND_PRODUCER;
            case SpanKind::KIND_INTERNAL:
                return null;
        }

        return null;
    }

    private static function convertOtelSpanDataToJaegerTags(SpanDataInterface $span): array
    {
        $tags = [];

        if ($span->getStatus()->getCode() !== StatusCode::STATUS_UNSET) {
            switch ($span->getStatus()->getCode()) {
                case StatusCode::STATUS_OK:
                    $tags[self::STATUS_CODE_TAG_KEY] = self::STATUS_OK;

                    break;
                case StatusCode::STATUS_ERROR:
                    $tags[self::KEY_ERROR_FLAG] = true;
                    $tags[self::STATUS_CODE_TAG_KEY] = self::STATUS_ERROR;

                    break;
            }

            if (!empty($span->getStatus()->getDescription())) {
                $tags[self::STATUS_DESCRIPTION_TAG_KEY] = $span->getStatus()->getDescription();
            }
        }

        if (!empty($span->getInstrumentationScope()->getName())) {
            $tags[SpanConverter::KEY_INSTRUMENTATION_SCOPE_NAME] = $span->getInstrumentationScope()->getName();
        }

        if ($span->getInstrumentationScope()->getVersion() !== null) {
            $tags[SpanConverter::KEY_INSTRUMENTATION_SCOPE_VERSION] = $span->getInstrumentationScope()->getVersion();
        }

        $jaegerSpanKind = self::convertOtelSpanKindToJaeger($span);
        if ($jaegerSpanKind !== null) {
            $tags[self::KEY_SPAN_KIND] = $jaegerSpanKind;
        }

        foreach ($span->getAttributes() as $k => $v) {
            $tags[$k] = $v;
        }

        foreach ($span->getResource()->getAttributes() as $k => $v) {
            $tags[$k] = $v;
        }
        foreach ($span->getInstrumentationScope()->getAttributes() as $k => $v) {
            $tags[$k] = $v;
        }

        $tags = self::buildTags($tags);

        return $tags;
    }

    private static function buildTags(array $tagPairs): array
    {
        $tags = [];
        foreach ($tagPairs as $key => $value) {
            $tags[] = TagFactory::create($key, $value);
        }

        return $tags;
    }

    private static function convertOtelEventsToJaegerLogs(SpanDataInterface $span): array
    {
        return array_map(
            function ($event) {
                return self::convertSingleOtelEventToJaegerLog($event);
            },
            $span->getEvents()
        );
    }

    private static function convertSingleOtelEventToJaegerLog(EventInterface $event): Log
    {
        $timestamp = TimeUtil::nanosToMicros($event->getEpochNanos());

        $eventValue = $event->getAttributes()->get(self::EVENT_ATTRIBUTE_KEY_NAMED_EVENT) ?? $event->getName();
        $attributes = $event->getAttributes()->toArray();
        $attributes[self::JAEGER_KEY_EVENT] = $eventValue;
        $attributesAsTags = self::buildTags($attributes);

        return new Log([
            'timestamp' => $timestamp,
            'fields' => $attributesAsTags,
        ]);
    }

    private static function convertOtelLinksToJaegerSpanReferences(SpanDataInterface $span): array
    {
        return array_map(
            function ($link) {
                return self::convertSingleOtelLinkToJaegerSpanReference($link);
            },
            $span->getLinks()
        );
    }

    private static function convertSingleOtelLinkToJaegerSpanReference(LinkInterface $link): SpanRef
    {
        [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
        ] = IdConverter::convertOtelToJaegerTraceIds($link->getSpanContext()->getTraceId());

        $integerSpanId = IdConverter::convertOtelToJaegerSpanId($link->getSpanContext()->getSpanId());

        return new SpanRef([
            'refType' => SpanRefType::FOLLOWS_FROM,
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
            'spanId' => $integerSpanId,
        ]);
    }
}
