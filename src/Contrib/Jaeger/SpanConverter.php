<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Jaeger\Thrift\Log;
use Jaeger\Thrift\Span as JTSpan;
use Jaeger\Thrift\SpanRef;
use Jaeger\Thrift\SpanRefType;
use Jaeger\Thrift\Tag;
use Jaeger\Thrift\TagType;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SDK\AbstractClock;
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
    const KEY_INSTRUMENTATION_LIBRARY_NAME = 'otel.library.name';
    const KEY_INSTRUMENTATION_LIBRARY_VERSION = 'otel.library.version';
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

        $startTime = AbstractClock::nanosToMicro($span->getStartEpochNanos());
        $duration = AbstractClock::nanosToMicro($span->getEndEpochNanos() - $span->getStartEpochNanos());

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

        $spanId = intval($span->getContext()->getSpanID(), 16);
        $parentSpanId = intval($span->getParentSpanId(), 16);

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

        if (!empty($span->getInstrumentationLibrary()->getName())) {
            $tags[SpanConverter::KEY_INSTRUMENTATION_LIBRARY_NAME] = $span->getInstrumentationLibrary()->getName();
        }

        if ($span->getInstrumentationLibrary()->getVersion() !== null) {
            $tags[SpanConverter::KEY_INSTRUMENTATION_LIBRARY_VERSION] = $span->getInstrumentationLibrary()->getVersion();
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

        $tags = self::buildTags($tags);

        return $tags;
    }

    private static function buildTags(array $tagPairs): array
    {
        $tags = [];
        foreach ($tagPairs as $key => $value) {
            $tags[] = self::buildTag($key, $value);
        }

        return $tags;
    }

    private static function buildTag(string $key, $value): Tag
    {
        return self::createJaegerTagInstance(
            $key,
            self::convertValueToTypeJaegerTagsSupport($value)
        );
    }

    private static function convertValueToTypeJaegerTagsSupport($value)
    {
        if (is_array($value)) {
            return self::serializeArrayToString($value);
        }

        return $value;
    }

    private static function createJaegerTagInstance(string $key, $value)
    {
        if (is_bool($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::BOOL,
                'vBool' => $value,
            ]);
        }

        if (is_integer($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::LONG,
                'vLong' => $value,
            ]);
        }

        if (is_numeric($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::DOUBLE,
                'vDouble' => $value,
            ]);
        }

        return new Tag([
            'key' => $key,
            'vType' => TagType::STRING,
            'vStr' => (string) $value,
        ]);
    }

    private static function serializeArrayToString(array $arrayToSerialize): string
    {
        return self::recursivelySerializeArray($arrayToSerialize);
    }

    private static function recursivelySerializeArray($value): string
    {
        if (is_array($value)) {
            return join(',', array_map(function ($val) {
                return self::recursivelySerializeArray($val);
            }, $value));
        }

        // Casting false to string makes an empty string
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // Floats will lose precision if their string representation
        // is >=14 or >=17 digits, depending on PHP settings.
        // Can also throw E_RECOVERABLE_ERROR if $value is an object
        // without a __toString() method.
        // This is possible because OpenTelemetry\Trace\Span does not verify
        // setAttribute() $value input.
        return (string) $value;
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
        $timestamp = AbstractClock::nanosToMicro($event->getEpochNanos());

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
