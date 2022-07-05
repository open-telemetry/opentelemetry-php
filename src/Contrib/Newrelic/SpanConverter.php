<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Newrelic;

use OpenTelemetry\SDK\Common\Time\Util as TimeUtil;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;

/**
 * @see https://docs.newrelic.com/docs/distributed-tracing/trace-api/report-new-relic-format-traces-trace-api/#new-relic-guidelines
 */
class SpanConverter implements SpanConverterInterface
{
    const STATUS_CODE_TAG_KEY = 'otel.status_code';
    const STATUS_DESCRIPTION_TAG_KEY = 'otel.status_description';

    private string $serviceName;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    public function convert(iterable $spans): array
    {
        $aggregate = [];
        foreach ($spans as $span) {
            $aggregate[] = $this->convertSpan($span);
        }

        return $aggregate;
    }

    private function convertSpan(SpanDataInterface $span): array
    {
        $spanParent = $span->getParentContext();

        $startTimestamp = TimeUtil::nanosToMillis($span->getStartEpochNanos());
        $endTimestamp = TimeUtil::nanosToMillis($span->getEndEpochNanos());

        $row = [
            'id' => $span->getSpanId(),
            'trace.id' => $span->getTraceId(),
            'attributes' => [
                'name' => $span->getName(),
                'service.name' => $this->serviceName,
                'parent.id' => $spanParent->isValid() ? $spanParent->getSpanId() : null,
                'timestamp' => $startTimestamp,
                'duration.ms' => (float) $endTimestamp - $startTimestamp,
                self::STATUS_CODE_TAG_KEY => $span->getStatus()->getCode(),
                self::STATUS_DESCRIPTION_TAG_KEY => $span->getStatus()->getDescription(),
            ],
        ];

        foreach ($span->getAttributes() as $k => $v) {
            $row['attributes'][$k] = $v;
        }

        foreach ($span->getResource()->getAttributes() as $k => $v) {
            $row['attributes'][$k] = $v;
        }
        foreach ($span->getInstrumentationScope()->getAttributes() as $k => $v) {
            $row['attributes'][$k] = $v;
        }

        /*
        foreach ($span->getEvents() as $event) {
            if (!array_key_exists('annotations', $row)) {
                $row['annotations'] = [];
            }
            $row['annotations'][] = [
                'timestamp' => (int) ($event->getTimestamp() / 1e6), // RealtimeClock in milliseconds
                'value' => $event->getName(),
            ];
        }
    */
        return $row;
    }
}
