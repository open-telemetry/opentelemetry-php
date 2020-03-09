<?php

declare(strict_types=1);

namespace OpenTelemetry\Exporter;

use Exception;
use InvalidArgumentException;
use OpenTelemetry\Trace\Span;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 */
class ZipkinExporter implements Exporter
{

    /**
     * @var $endpoint array to send Spans to
     */
    private $endpoint;
    private $name;

    public function __construct($name, string $endpointDsn)
    {
        $parsedDsn = parse_url($endpointDsn);

        if (!is_array($parsedDsn)) {
            throw new InvalidArgumentException('Unable to parse provided DSN');
        }

        $this->setEndpoint($parsedDsn);
        $this->name = $name;
    }

    /**
     * Exports the provided Span data via the Zipkin protocol
     *
     * @param iterable<Span> $spans Array of Spans
     * @return int return code, defined on the Exporter interface
     */
    public function export(iterable $spans) : int
    {
        if (empty($spans)) {
            return Exporter::SUCCESS;
        }

        $convertedSpans = [];
        foreach ($spans as &$span) {
            array_push($convertedSpans, $this->convertSpan($span));
        }

        try {
            $json = json_encode($convertedSpans);
            $contextOptions = [
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $json,
                ],
            ];
            $context = stream_context_create($contextOptions);
            file_get_contents($this->getEndpointUrl(), false, $context);
        } catch (Exception $e) {
            return Exporter::FAILED_RETRYABLE;
        }

        return Exporter::SUCCESS;
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
            'localEndpoint' => [
                'serviceName' => $this->name,
                'port'  => $this->getEndpoint()['port'] ?? 0,
            ],
            'name' => $span->getName(),
            'timestamp' => (int) round($span->getStart()*1000000),
            'duration' => (int) round($span->getEnd()*1000000) - round($span->getStart()*1000000),
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
     * @return array |null
     */
    public function getEndpoint(): ?array
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
        if (!isset($endpoint['scheme'])
            || !isset($endpoint['host'])
            || !isset($endpoint['port'])
            || !isset($endpoint['path'])
        ) {
            throw new InvalidArgumentException('Endpoint should have scheme, host, port and path');
        }

        $this->endpoint = $endpoint;

        return $this;
    }

    protected function getEndpointUrl(): string
    {
        return sprintf(
            '%s://%s:%s%s',
            $this->endpoint['scheme'],
            $this->endpoint['host'],
            $this->endpoint['port'],
            $this->endpoint['path']
        );
    }
}
