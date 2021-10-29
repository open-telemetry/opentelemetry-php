<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace;
use ReflectionClass;

class ConsoleSpanExporter implements Trace\SpanExporterInterface
{
    private $running = true;

    /** @inheritDoc */
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
     * @param iterable<\OpenTelemetry\API\Trace\EventInterface> $events
     * @return array
     */
    private function friendlyEvents(iterable $events)
    {
        $tmp = [];

        foreach ($events as $event) {
            array_push($tmp, [
                'name' => $event->getName(),
                'timestamp' => $event->getEpochNanos(),
                'attributes' => $this->friendlyAttributes($event->getAttributes()),
            ]);
        }

        return $tmp;
    }

    /**
     * @param \OpenTelemetry\API\Trace\AttributesInterface $attributes
     * @return array
     */
    private function friendlyAttributes(\OpenTelemetry\API\Trace\AttributesInterface $attributes)
    {
        $tmp = [];

        foreach ($attributes as $attribute) {
            array_push($tmp, [
                'key' => $attribute->getKey(),
                'value' => $attribute->getValue(),
            ]);
        }

        return $tmp;
    }

    /**
     * Translates SpanKind from it's integer representation to a more human friendly string.
     *
     * @param int $kind
     * @return string
     */
    private function friendlyKind(int $kind)
    {
        $spanKinds = (new ReflectionClass(SpanKind::class))->getConstants();

        $kindSpans = array_flip($spanKinds);

        return $kindSpans[$kind];
    }

    /**
     * @param \OpenTelemetry\SDK\Resource\ResourceInfo $resource
     * @return array
     */
    private function friendlyResource(\OpenTelemetry\SDK\Resource\ResourceInfo $resource): array
    {
        $tmp = [];

        foreach ($resource->getAttributes()->getIterator() as $attribute) {
            $tmp[$attribute->getKey()] = $attribute->getValue();
        }

        return $tmp;
    }

    /**
     * friendlySpan does the heavy lifting converting a span into an array
     *
     * @param Trace\SpanDataInterface $span
     * @return array
     */
    private function friendlySpan(Trace\SpanDataInterface $span)
    {
        $parent_span = $span->getParentContext();

        $parent_span_id = $parent_span->isValid() ? $parent_span->getSpanId() : null;

        $foo = $span->getEvents();
        var_dump($span->getResource()->getAttributes());

        return [
            'name' => $span->getName(),
            'context' => [
                'trace_id' => $span->getContext()->getTraceId(),
                'span_id' => $span->getContext()->getSpanId(),
                'trace_state' => $span->getContext()->getTraceState(),
            ],
            'resource' => $this->friendlyResource($span->getResource()),
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
