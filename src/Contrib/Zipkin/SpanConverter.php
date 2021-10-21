<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use function max;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SDK\Trace\AbstractClock;
use OpenTelemetry\SDK\Trace\SpanDataInterface;

class SpanConverter
{
    const STATUS_CODE_TAG_KEY = 'otel.status_code';
    const STATUS_DESCRIPTION_TAG_KEY = 'error';
    const KEY_INSTRUMENTATION_LIBRARY_NAME = 'otel.library.name';
    const KEY_INSTRUMENTATION_LIBRARY_VERSION = 'otel.library.version';

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

        // Zipkin tags must be strings, but opentelemetry
        // accepts strings, booleans, numbers, and lists of each.
        if (is_array($value)) {
            return implode(',', array_map([$this, 'sanitiseTagValue'], $value));
        }

        // Floats will lose precision if their string representation
        // is >=14 or >=17 digits, depending on PHP settings.
        // Can also throw E_RECOVERABLE_ERROR if $value is an object
        // without a __toString() method.
        // This is possible because OpenTelemetry\API\Trace\Span does not verify
        // setAttribute() $value input.
        return (string) $value;
    }

    public function convert(SpanDataInterface $span)
    {
        $spanParent = $span->getParentContext();

        $startTimestamp = AbstractClock::nanosToMicro($span->getStartEpochNanos());
        $endTimestamp = AbstractClock::nanosToMicro($span->getEndEpochNanos());

        $row = [
            'id' => $span->getSpanId(),
            'kind' => SpanConverter::toSpanKind($span),
            'traceId' => $span->getTraceId(),
            'localEndpoint' => [
                'serviceName' => $this->serviceName,
            ],
            'name' => $span->getName(),
            'timestamp' => $startTimestamp,
            'duration' => max(1, $endTimestamp - $startTimestamp),
            'tags' => [],
        ];

        if ($spanParent->isValid()) {
            $row['parentId'] = $spanParent->getSpanId();
        }

        if ($span->getStatus()->getCode() !== StatusCode::STATUS_UNSET) {
            $row['tags'][self::STATUS_CODE_TAG_KEY] = $span->getStatus()->getCode();
        }

        if ($span->getStatus()->getCode() === StatusCode::STATUS_ERROR) {
            $row['tags'][self::STATUS_DESCRIPTION_TAG_KEY] = $span->getStatus()->getDescription();
        }

        if (!empty($span->getInstrumentationLibrary()->getName())) {
            $row[SpanConverter::KEY_INSTRUMENTATION_LIBRARY_NAME] = $span->getInstrumentationLibrary()->getName();
        }

        if ($span->getInstrumentationLibrary()->getVersion() !== null) {
            $row[SpanConverter::KEY_INSTRUMENTATION_LIBRARY_VERSION] = $span->getInstrumentationLibrary()->getVersion();
        }

        foreach ($span->getAttributes() as $k => $v) {
            $row['tags'][$k] = $this->sanitiseTagValue($v->getValue());
        }

        foreach ($span->getEvents() as $event) {
            if (!array_key_exists('annotations', $row)) {
                $row['annotations'] = [];
            }
            $row['annotations'][] = [
                'timestamp' => AbstractClock::nanosToMicro($event->getEpochNanos()),
                'value' => $event->getName(),
            ];
        }

        return $row;
    }

    private static function toSpanKind(SpanDataInterface $span): ?int
    {
        switch ($span->getKind()) {
          case SpanKind::KIND_SERVER:
            return SpanKind::KIND_SERVER;
          case SpanKind::KIND_CLIENT:
            return SpanKind::KIND_CLIENT;
          case SpanKind::KIND_PRODUCER:
            return SpanKind::KIND_PRODUCER;
          case SpanKind::KIND_CONSUMER:
            return SpanKind::KIND_CONSUMER;
          case SpanKind::KIND_INTERNAL:
            return null;
        }
        
        return null;
    }
}
