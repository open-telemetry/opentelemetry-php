<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\SDK\Trace;

class ConsoleSpanExporter implements Trace\Exporter
{
    private $running = true;

    /**
     * Exports the provided Span data via the OTLP protocol
     *
     * @param iterable<Trace\ImmutableSpan> $spans Array of Spans
     * @return int return code, defined on the Exporter interface
     */
    public function export(iterable $spans): int
    {
        foreach ($spans as $span) {
            print(json_encode($this->friendlySpan($span), JSON_PRETTY_PRINT) . PHP_EOL);
        }

        return Trace\Exporter::SUCCESS;
    }

    public function shutdown(): void
    {
        $this->running = false;
    }

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null)
    {
        return false;
    }

    private function friendlySpan(Trace\SpanDataInterface $span)
    {
        $parent_span = $span->getParentContext();

        $parent_span_id = $parent_span->isValid() ? $parent_span->getTraceId() : null;

        return [
            'name' => $span->getName(),
            'context' => [
                'trace_id' => $span->getContext()->getTraceId(),
                'span_id' => $span->getContext()->getSpanId(),
                'trace_state' => $span->getContext()->getTraceState(),
            ],
            'parent_span_id' => $parent_span_id ? $parent_span_id : '',
            'kind' => $span->getKind(),
            'start' => $span->getStartEpochNanos(),
            'end' => $span->getEndEpochNanos(),
            'attributes' => $span->getAttributes(),
            'status' => $span->getStatus(),
            'events' => $span->getEvents(),
        ];
    }
}
