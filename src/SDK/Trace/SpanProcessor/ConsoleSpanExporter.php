<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace;
use \ReflectionClass;

class ConsoleSpanExporter implements Trace\SpanExporterInterface
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

        return Trace\SpanExporterInterface::STATUS_SUCCESS;
    }

    /** @inheritDoc */
    public function shutdown(): bool
    {
        $this->running = false;

        return true;
    }

    /** @inheritDoc */
    public function forceFlush(): bool
    {
        return true;
    }

    public static function fromConnectionString(string $endpointUrl = null, string $name = null, $args = null)
    {
        return false;
    }

    /**
     * @param iterable<API\EventInterface> $events
     * @return array
     */
    private function friendlyEvents(iterable $events) {

        $tmp = [];

        foreach($events as $event) {
            array_push($tmp, [
                'name' => $event->getName(),
                'timestamp' => $event->getEpochNanos(),
                'attributes' => $this->friendlyAttributes($event->getAttributes())
            ]);
        }

        return $tmp;

    }

    /**
     * @param iterable<API\AttributesInterface> $attributes
     * @return array
     */
    private function friendlyAttributes(iterable $attributes) {
        $tmp = [];

        foreach($attributes as $attribute) {
            array_push($tmp, [
                'key' => $attribute->getKey(),
                'value' => $attribute->getValue()
            ]);
        }

        return $tmp;
    }

    private function friendlyKind(int $kind)
    {
        $spanKinds = (new ReflectionClass(SpanKind::class))->getConstants();

        $kindSpans = array_flip($spanKinds);

        return $kindSpans[$kind];
    }

    private function friendlySpan(Trace\SpanDataInterface $span)
    {
        $parent_span = $span->getParentContext();

        $parent_span_id = $parent_span->isValid() ? $parent_span->getTraceId() : null;

        $foo = $span->getEvents();

        return [
            'name' => $span->getName(),
            'context' => [
                'trace_id' => $span->getContext()->getTraceId(),
                'span_id' => $span->getContext()->getSpanId(),
                'trace_state' => $span->getContext()->getTraceState(),
            ],
            'parent_span_id' => $parent_span_id ? $parent_span_id : '',
            'kind' => $this->friendlyKind($span->getKind()),
            'start' => $span->getStartEpochNanos(),
            'end' => $span->getEndEpochNanos(),
            'attributes' => $this->friendlyAttributes($span->getAttributes()),
            'status' => [
                'code' => $span->getStatus()->getCode(),
                'description' => $span->getStatus()->getDescription(),
            ],
            'events' => $this->friendlyEvents($span->getEvents()),
        ];
    }
}
