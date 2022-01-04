<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Jaeger\Thrift\Span as JTSpan;
use Jaeger\Thrift\Tag;
use Jaeger\Thrift\TagType;
use OpenTelemetry\SDK\Trace\SpanDataInterface;

class SpanConverter
{
    const STATUS_CODE_TAG_KEY = 'op.status_code';
    const STATUS_DESCRIPTION_TAG_KEY = 'op.status_description';
    /**
     * @var string
     */
    private $serviceName;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
    * Convert span to Jaeger Thrift Span format
    */
    public function convert(SpanDataInterface $span): JTSpan
    {
        $references = $tags = $logs = [];
        $startTime = (int) ($span->getStartEpochNanos() / 1e3); // microseconds
        $duration = (int) (($span->getEndEpochNanos() - $span->getStartEpochNanos()) / 1e3); // microseconds
        $tags = [
            self::STATUS_CODE_TAG_KEY => $span->getStatus()->getCode(),
            self::STATUS_DESCRIPTION_TAG_KEY => $span->getStatus()->getDescription(),
        ];

        foreach ($span->getAttributes() as $k => $v) {
            $tags[$k] = $this->sanitiseTagValue($v->getValue());
        }
        $tags = $this->buildTags($tags);

        $traceId = $span->getContext()->getTraceID();
        $parentSpanId = $span->getParentSpanId();
        $spanId = $span->getContext()->getSpanID();

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
            'traceIdLow' => (is_array($traceId) ? $traceId['low'] : $traceId),
            'traceIdHigh' => (is_array($traceId) ? $traceId['high'] : 0),
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

    private function buildTags($tagPairs)
    {
        $tags = [];
        foreach ($tagPairs as $key => $value) {
            $tags[] = $this->buildTag($key, $value);
        }

        return $tags;
    }

    private function buildTag($key, $value)
    {
        if (is_bool($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::BOOL,
                'vBool' => $value,
            ]);
        } elseif (is_string($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::STRING,
                'vStr' => $value,
            ]);
        } elseif (null === $value) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::STRING,
                'vStr' => '',
            ]);
        } elseif (is_integer($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::LONG,
                'vLong' => $value,
            ]);
        } elseif (is_numeric($value)) {
            return new Tag([
                'key' => $key,
                'vType' => TagType::DOUBLE,
                'vDouble' => $value,
            ]);
        }

        error_log('Cannot build tag for ' . $key . ' of type ' . gettype($value));

        throw new \Exception('unsupported tag type');
    }
} 