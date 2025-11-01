<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\EventInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\StatusDataInterface;
use ReflectionClass;

class FriendlySpanConverter implements SpanConverterInterface
{
    private const NAME_ATTR = 'name';
    private const CONTEXT_ATTR = 'context';
    private const TRACE_ID_ATTR = 'trace_id';
    private const SPAN_ID_ATTR = 'span_id';
    private const TRACE_STATE_ATTR = 'trace_state';
    private const TRACE_FLAGS_ATTR = 'trace_flags';
    private const RESOURCE_ATTR = 'resource';
    private const PARENT_SPAN_ATTR = 'parent_span_id';
    private const KIND_ATTR = 'kind';
    private const START_ATTR = 'start';
    private const END_ATTR = 'end';
    private const ATTRIBUTES_ATTR = 'attributes';
    private const STATUS_ATTR = 'status';
    private const CODE_ATTR = 'code';
    private const DESCRIPTION_ATTR = 'description';
    private const EVENTS_ATTR = 'events';
    private const TIMESTAMP_ATTR = 'timestamp';
    private const LINKS_ATTR = 'links';
    private const SCHEMA_URL_ATTR = 'schema_url';

    #[\Override]
    public function convert(iterable $spans): array
    {
        $aggregate = [];
        foreach ($spans as $span) {
            $aggregate[] = $this->convertSpan($span);
        }

        return $aggregate;
    }

    /**
     * convertSpan does the heavy lifting converting a span into an array
     */
    private function convertSpan(SpanDataInterface $span): array
    {
        return [
            self::NAME_ATTR => $span->getName(),
            self::CONTEXT_ATTR => $this->convertContext($span->getContext()),
            self::RESOURCE_ATTR => $this->convertResource($span->getResource()),
            self::PARENT_SPAN_ATTR => $this->covertParentContext($span->getParentContext()),
            self::KIND_ATTR => $this->convertKind($span->getKind()),
            self::START_ATTR => $span->getStartEpochNanos(),
            self::END_ATTR => $span->getEndEpochNanos(),
            self::ATTRIBUTES_ATTR => $this->convertAttributes($span->getAttributes()),
            self::STATUS_ATTR => $this->covertStatus($span->getStatus()),
            self::EVENTS_ATTR => $this->convertEvents($span->getEvents()),
            self::LINKS_ATTR => $this->convertLinks($span->getLinks()),
            self::SCHEMA_URL_ATTR => $this->convertSchemaUrl($span->getInstrumentationScope()->getSchemaUrl()),
        ];
    }

    private function convertContext(SpanContextInterface $context): array
    {
        return [
            self::TRACE_ID_ATTR => $context->getTraceId(),
            self::SPAN_ID_ATTR => $context->getSpanId(),
            self::TRACE_STATE_ATTR => (string) $context->getTraceState(),
            self::TRACE_FLAGS_ATTR => $context->getTraceFlags(),
        ];
    }

    private function convertResource(ResourceInfo $resource): array
    {
        return $resource->getAttributes()->toArray();
    }

    private function covertParentContext(SpanContextInterface $context): string
    {
        return $context->isValid() ? $context->getSpanId() : '';
    }

    /**
     * Translates SpanKind from its integer representation to a more human friendly string.
     */
    private function convertKind(int $kind): string
    {
        return  array_flip(
            (new ReflectionClass(SpanKind::class))
                ->getConstants()
        )[$kind];
    }

    private function convertAttributes(AttributesInterface $attributes): array
    {
        return $attributes->toArray();
    }

    private function covertStatus(StatusDataInterface $status): array
    {
        return [
            self::CODE_ATTR => $status->getCode(),
            self::DESCRIPTION_ATTR => $status->getDescription(),
        ];
    }

    /**
     * @param array<EventInterface> $events
     */
    private function convertEvents(array $events): array
    {
        $result = [];

        foreach ($events as $event) {
            $result[] = [
                self::NAME_ATTR => $event->getName(),
                self::TIMESTAMP_ATTR => $event->getEpochNanos(),
                self::ATTRIBUTES_ATTR => $this->convertAttributes($event->getAttributes()),
            ];
        }

        return $result;
    }

    /**
     * @param array<LinkInterface> $links
     */
    private function convertLinks(array $links): array
    {
        $result = [];

        foreach ($links as $link) {
            $result[] = [
                self::CONTEXT_ATTR => $this->convertContext($link->getSpanContext()),
                self::ATTRIBUTES_ATTR => $this->convertAttributes($link->getAttributes()),
            ];
        }

        return $result;
    }

    /**
     * @param string|null $schemaUrl
     */
    private function convertSchemaUrl(?string $schemaUrl): string
    {
        return $schemaUrl ?? '';
    }
}
