<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Zipkin;

use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Trace\Span;

class SpanConverter
{
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
        $moment = (new Clock())->moment();
        $start_realtime = (int)($span->getStartTimestamp());
        $end_realtime = (int)($span->getEndTimestamp());
        $elapsed_realtime = $end_realtime - $start_realtime;

        $row = [
            'id' => $span->getContext()->getSpanId(),
            'traceId' => $span->getContext()->getTraceId(),
            'parentId' => $span->getParent() ? $span->getParent()->getSpanId() : null,
            'localEndpoint' => [
                'serviceName' => $this->serviceName,
            ],
            'name' => $span->getSpanName(),
            'timestamp' => $moment[0], // RealtimeClock
            'duration' => $elapsed_realtime,
        ];

        foreach ($span->getAttributes() as $k => $v) {
            if (!array_key_exists('tags', $row)) {
                $row['tags'] = [];
            }
            $v = $v->getValue();
            if (is_bool($v)) {
                $v = (string) $v;
            }
            $row['tags'][$k] = $v;
        }

        foreach ($span->getEvents() as $event) {
            if (!array_key_exists('annotations', $row)) {
                $row['annotations'] = [];
            }
            $row['annotations'][] = [
                'timestamp' => $moment[1] * 1000,
                'value' => $event->getName(),
            ];
        }

        return $row;
    }
}
