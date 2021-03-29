<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Newrelic;

use OpenTelemetry\Trace\Span;

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

    public function convert(Span $span)
    {
        $spanParent = $span->getParent();
        $row = [
            'id' => $span->getContext()->getSpanId(),
            'trace.id' => $span->getContext()->getTraceId(),
            'attributes' => [
                'name' => $span->getSpanName(),
                'service.name' => $this->serviceName,
                'parent.id' => $spanParent ? $spanParent->getSpanId() : null,
                'timestamp' => ($span->getStartEpochTimestamp()  / 1e6), // RealtimeClock in milliseconds
                'duration.ms' => (($span->getEnd() - $span->getStart())  / 1e6), // Diff in milliseconds
                self::STATUS_CODE_TAG_KEY => $span->getStatus()->getCanonicalStatusCode(),
                self::STATUS_DESCRIPTION_TAG_KEY => $span->getStatus()->getStatusDescription(),
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
