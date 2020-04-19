<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Zipkin;

use OpenTelemetry\Sdk\Internal\Time;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Trace as API;

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

    /**
     * @param API\Span|Span $span
     * @return array
     */
    public function convert(API\Span $span)
    {
        $row = [
            'id' => $span->getContext()->getSpanId(),
            'traceId' => $span->getContext()->getTraceId(),
            'parentId' => $span->getParent() ? $span->getParent()->getSpanId() : null,
            'localEndpoint' => [
                'serviceName' => $this->serviceName,
            ],
            'name' => $span->getSpanName(),
            'timestamp' => $span->getStartTimestamp()->to(Time::MICROSECOND),
            'duration' => $span->getDuration()->to(Time::MICROSECOND),
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
                'timestamp' => $span->getStartTimestamp()->to(Time::MICROSECOND),
                'value' => $event->getName(),
            ];
        }

        return $row;
    }
}
