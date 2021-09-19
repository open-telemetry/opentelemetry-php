<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Newrelic;

use function max;
use OpenTelemetry\Sdk\Trace\SpanData;

class SpanConverter
{
    const STATUS_CODE_TAG_KEY = 'otel.status_code';
    const STATUS_DESCRIPTION_TAG_KEY = 'otel.status_description';

    /**
     * @var string
     */
    private $serviceName;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    public function convert(SpanData $span)
    {
        $spanParent = $span->getParentContext();

        $startTimestamp = $span->getStartEpochNanos() / 1e6;
        $endTimestamp = $span->getEndEpochNanos() / 1e6;

        $row = [
            'id' => $span->getSpanId(),
            'trace.id' => $span->getTraceId(),
            'attributes' => [
                'name' => $span->getName(),
                'service.name' => $this->serviceName,
                'parent.id' => $spanParent->isValid() ? $spanParent->getSpanId() : null,
                'timestamp' => $startTimestamp, // RealtimeClock in milliseconds
                'duration.ms' => max(1, $startTimestamp - $endTimestamp), // Diff in milliseconds
                self::STATUS_CODE_TAG_KEY => $span->getStatus()->getCode(),
                self::STATUS_DESCRIPTION_TAG_KEY => $span->getStatus()->getDescription(),
            ],
        ];

        foreach ($span->getAttributes() as $k => $v) {
            $row['attributes'][$k] = $v->getValue();
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
