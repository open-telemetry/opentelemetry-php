<?php

declare(strict_types=1);

namespace OpenTelemetry\Exporter;

use OpenTelemetry\Exporter;
use OpenTelemetry\Tracing\Span;
use OpenTelemetry\Tracing\Tracer;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 */
class ZipkinExporter implements Exporter
{

    /**
     * @var endpoint to send Spans to
     */
    private $endpoint;

    /**
     * Exports the provided Span data via the Zipkin protocol
     *
     * @param Span ...$spans Array of Spans
     * @return int return code, defined on the Exporter interface
     */
    public function export(Span ...$spans) : int
    {
        $convertedSpans = [];
        foreach ($spans as &$span) {
            array_push($convertedSpans, converSpan($span));
        }

        #todo: format into JSON paylod for zipkin: (see https://github.com/census-ecosystem/opencensus-php-exporter-zipkin/blob/master/src/ZipkinExporter.php#L143)
    }

    #todo: this method is required via the spec, but not sure if needed
    /**
     * Shutdown the export
     *
     * @return int
     */
    public function shutdown() : int
    {

    }

    /**
     * Converts spans to Zipkin format for export
     *
     * @param Span $span
     * @return array
     */
    private function convertSpan(Span $span) : array
    {
        $row = [
            'id' => $span->getContext()->getSpanId(),
            'traceId' => $span->getContext()->getTraceId(),
            'parentId' => $span->getParentContext()
                ? $span->getParentContext()->getSpanId()
                : null,
            'localEndpoint' => $this->getEndpoint(),
            'name' => $span->getName(),
            'timestamp' => (integer) round($span->getStart()*1000000),
            'duration' => (integer) round($span->getEnd()*1000000) - round($span->getStart()*1000000),
        ];

        foreach ($span->getAttributes() as $k => $v) {
            if (!array_key_exists('tags', $row)) {
                $row['tags'] = [];
            }
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
                'timestamp' => round($event->getTimestamp()*1000000),
                'value' => $event->getName(),
            ];
        }

        return $row;
    }

    /**
     * Gets the configured endpoint for the Zipkin exporter
     *
     * @return endpoint
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Sets the configured endpoint for the zipkin exportedr
     *
     * @param array $endpoint
     * @return $this
     */
    public function setEndpoint(array $endpoint) : self
    {
        $this->endpoint = $endpoint;
        return $this;
    }
}
