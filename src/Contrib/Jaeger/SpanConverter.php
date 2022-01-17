<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Jaeger\Thrift\Span as JTSpan;
use Jaeger\Thrift\Tag;
use Jaeger\Thrift\TagType;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use RuntimeException;

class SpanConverter
{
    const STATUS_CODE_TAG_KEY = 'otel.status_code';
    const STATUS_DESCRIPTION_TAG_KEY = 'otel.status_description';
    const KEY_INSTRUMENTATION_LIBRARY_NAME = 'otel.library.name';
    const KEY_INSTRUMENTATION_LIBRARY_VERSION = 'otel.library.version';

    public function __construct()
    {
        self::checkIfPHPSupports64BitIntegers();
    }

    /**
    * Convert span to Jaeger Thrift Span format
    */
    public function convert(SpanDataInterface $span): JTSpan
    {
        $references = $tags = $logs = [];
        $startTime = AbstractClock::nanosToMicro($span->getStartEpochNanos());
        $duration = AbstractClock::nanosToMicro($span->getEndEpochNanos() - $span->getStartEpochNanos());

        $tags = [];

        if ($span->getStatus()->getCode() !== StatusCode::STATUS_UNSET) {
            switch ($span->getStatus()->getCode()) {
                case StatusCode::STATUS_OK:
                    $tags[self::STATUS_CODE_TAG_KEY] = 'OK';

                    break;
                case StatusCode::STATUS_ERROR:
                    //This is where the error flag section of the spec should be implemented - https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/sdk_exporters/jaeger.md#error-flag, see Go for reference - https://github.com/open-telemetry/opentelemetry-go/blob/main/exporters/jaeger/jaeger.go#L154
                    $tags[self::STATUS_CODE_TAG_KEY] = 'ERROR';

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

        foreach ($span->getAttributes() as $k => $v) {
            $tags[$k] = $this->sanitiseTagValue($v);
        }
        $tags = $this->buildTags($tags);

        [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
            'spanId' => $spanId,
            'parentSpanId' => $parentSpanId,
        ] = self::convertOtlpToJaegerIds($span);

        //NOTE - the below commented out code may be a useful reference when updating this method to be spec compliant

        // foreach ($span->getEvents() as $event) {
        //     $logs = [
        //         'timestamp' => (int) ($event->getTimestamp() / 1e3), // RealtimeClock in microseconds
        //         'fields' => $event->getName(),
        //     ];
        // }

        //$type = ($parentSpanId != null) ? SpanRefType::CHILD_OF : SpanRefType::FOLLOWS_FROM;

        // $references = (array) new SpanRef([
        //     "refType" => $type,
        //     "traceIdLow" => (is_array($traceId) ? $traceId["low"] : $traceId),
        //     "traceIdHigh" => (is_array($traceId) ? $traceId["high"] : $traceId),
        //     "spanId" => $span->getContext()->getSpanID(),
        // ]);

        return new JTSpan([
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
            'spanId' => $spanId,
            'parentSpanId' => $parentSpanId,
            'operationName' => $span->getName(),
            'references' => $references,
            'flags' => $span->getContext()->getTraceFlags(),
            'startTime' => $startTime,
            'duration' => ($duration < 0) ? 0 : $duration,
            'tags' => $tags,
            'logs' => $logs,
        ]);
    }

    private function sanitiseTagValue($value): string
    {
        // Casting false to string makes an empty string
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // Zipkin tags must be strings, but opentelemetry
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

    private function buildTags(array $tagPairs): array
    {
        $tags = [];
        foreach ($tagPairs as $key => $value) {
            $tags[] = $this->buildTag($key, $value);
        }

        return $tags;
    }

    private function buildTag(string $key, string $value): Tag
    {
        return new Tag([
            'key' => $key,
            'vType' => TagType::STRING,
            'vStr' => $value,
        ]);

        //NOTE - the below commented out code may be a useful reference when updating this method to be spec compliant

        // if (is_bool($value)) {
        //     return new Tag([
        //         'key' => $key,
        //         'vType' => TagType::BOOL,
        //         'vBool' => $value,
        //     ]);
        // } elseif (is_string($value)) {
        //     return new Tag([
        //         'key' => $key,
        //         'vType' => TagType::STRING,
        //         'vStr' => $value,
        //     ]);
        // } elseif (null === $value) {
        //     return new Tag([
        //         'key' => $key,
        //         'vType' => TagType::STRING,
        //         'vStr' => '',
        //     ]);
        // } elseif (is_integer($value)) {
        //     return new Tag([
        //         'key' => $key,
        //         'vType' => TagType::LONG,
        //         'vLong' => $value,
        //     ]);
        // } elseif (is_numeric($value)) {
        //     return new Tag([
        //         'key' => $key,
        //         'vType' => TagType::DOUBLE,
        //         'vDouble' => $value,
        //     ]);
        // }

        // error_log('Cannot build tag for ' . $key . ' of type ' . gettype($value));

        // throw new \Exception('unsupported tag type');
    }

    private static function checkIfPHPSupports64BitIntegers(): void
    {
        if (PHP_INT_SIZE < 8) {
            $humanReadableIntSize = PHP_INT_SIZE*8;

            throw new RuntimeException("Integrating with Jaeger requires usage of 64 bit integers, but your current platform is $humanReadableIntSize bit. See https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/sdk_exporters/jaeger.md#ids for more information.");
        }
    }

    private static function convertOtlpToJaegerIds(SpanDataInterface $span): array
    {
        [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh
        ] = self::convertOtlpToJaegerTraceIds($span->getContext()->getTraceID());

        $spanId = intval($span->getContext()->getSpanID(), 16);
        $parentSpanId = intval($span->getParentSpanId(), 16);

        return [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
            'spanId' => $spanId,
            'parentSpanId' => $parentSpanId,
        ];
    }

    private static function convertOtlpToJaegerTraceIds(string $traceId): array
    {
        $traceIdLow = intval(substr($traceId, 0, 16), 16);
        $traceIdHigh = intval(substr($traceId, 16, 32), 16);

        return [
            'traceIdLow' => $traceIdLow,
            'traceIdHigh' => $traceIdHigh,
        ];
    }
}
