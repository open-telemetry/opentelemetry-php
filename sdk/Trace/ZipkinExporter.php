<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use Exception;
use InvalidArgumentException;

use OpenTelemetry\Trace as API;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 */
class ZipkinExporter implements Exporter
{
    /** @var string */
    private $endpointUrl;
    /** @var string */
    private $name;

    public function __construct($name, string $endpointUrl)
    {
        $this->setEndpoint($endpointUrl);
        $this->name = $name;
    }

    /**
     * Exports the provided Span data via the Zipkin protocol
     *
     * @param iterable<API\Span> $spans Array of Spans
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
            file_get_contents($this->endpointUrl, false, $context);
        } catch (Exception $e) {
            return Exporter::FAILED_RETRYABLE;
        }

        return Exporter::SUCCESS;
    }

    /**
     * Converts spans to Zipkin format for export
     *
     * @param API\Span $span
     * @return array
     */
    private function convertSpan(API\Span $span) : array
    {
        $row = [
            'id' => $span->getContext()->getSpanId(),
            'traceId' => $span->getContext()->getTraceId(),
            'parentId' => $span->getParent() ? $span->getParent()->getSpanId() : null,
            'localEndpoint' => [
                'serviceName' => $this->name,
                'port'  => $this->getEndpoint()['port'] ?? 0,
            ],
            'name' => $span->getSpanName(),
            'timestamp' => (int) round((float) $span->getStartTimestamp()),
            'duration' => (int) round((float) $span->getEndTimestamp()) - round((float) $span->getStartTimestamp()),
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
                'timestamp' => $event->getTimestamp(),
                'value' => $event->getName(),
            ];
        }

        return $row;
    }

    /**
     * Gets the configured endpoint for the Zipkin exporter
     *
     * @return string |null
     */
    public function getEndpoint(): ?string
    {
        return $this->endpointUrl;
    }

    /**
     * Sets the configured endpoint for the zipkin exportedr
     *
     * @param string $endpointUrl
     * @return $this
     */
    public function setEndpoint(string $endpointUrl) : self
    {
        $parsedDsn = parse_url($endpointUrl);

        if (!is_array($parsedDsn)) {
            throw new InvalidArgumentException('Unable to parse provided DSN');
        }

        if (!isset($parsedDsn['scheme'])
            || !isset($parsedDsn['host'])
            || !isset($parsedDsn['port'])
            || !isset($parsedDsn['path'])
        ) {
            throw new InvalidArgumentException('Endpoint should have scheme, host, port and path');
        }

        $this->endpointUrl = $endpointUrl;

        return $this;
    }

    public function shutdown(): void
    {
        // TODO: Implement shutdown() method.
    }
}
