<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function max;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource\ResourceInfo;

/**
 * @psalm-immutable
 */
final class ImmutableSpan implements SpanData
{
    private Span $span;

    /** @var non-empty-string */
    private string $name;

    /** @var list<API\Event> */
    private array $events;

    /** @var list<API\Link> */
    private array $links;

    private API\Attributes $attributes;
    private int $totalAttributeCount;
    private int $totalRecordedEvents;
    private StatusData $status;
    private int $endEpochNanos;
    private bool $hasEnded;

    /**
     * @internal
     * @psalm-internal OpenTelemetry\Sdk
     *
     * @param non-empty-string $name
     * @param list<API\Link> $links
     * @param list<API\Event> $events
     */
    public function __construct(
        Span $span,
        string $name,
        array $links,
        array $events,
        API\Attributes $attributes,
        int $totalAttributeCount,
        int $totalRecordedEvents,
        StatusData $status,
        int $encEpochNanos,
        bool $hasEnded
    ) {
        $this->span = $span;
        $this->name = $name;
        $this->links = $links;
        $this->events = $events;
        $this->attributes = $attributes;
        $this->totalAttributeCount = $totalAttributeCount;
        $this->totalRecordedEvents = $totalRecordedEvents;
        $this->status = $status;
        $this->endEpochNanos = $encEpochNanos;
        $this->hasEnded = $hasEnded;
    }

    public function getKind(): int
    {
        return $this->span->getKind();
    }

    public function getContext(): API\SpanContext
    {
        return $this->span->getContext();
    }

    public function getParentContext(): API\SpanContext
    {
        return $this->span->getParentContext();
    }

    public function getTraceId(): string
    {
        return $this->getContext()->getTraceId();
    }

    public function getSpanId(): string
    {
        return $this->getContext()->getSpanId();
    }

    public function getParentSpanId(): string
    {
        return $this->getParentContext()->getSpanId();
    }

    public function getStartEpochNanos(): int
    {
        return $this->span->getStartEpochNanos();
    }

    public function getEndEpochNanos(): int
    {
        return $this->endEpochNanos;
    }

    public function getInstrumentationLibrary(): InstrumentationLibrary
    {
        return $this->span->getInstrumentationLibrary();
    }

    public function getResource(): ResourceInfo
    {
        return $this->span->getResource();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @inheritDoc */
    public function getLinks(): array
    {
        return $this->links;
    }

    /** @inheritDoc */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function getAttributes(): API\Attributes
    {
        return $this->attributes;
    }

    public function getTotalDroppedAttributes(): int
    {
        return max(0, $this->totalAttributeCount - count($this->attributes));
    }

    public function getTotalDroppedEvents(): int
    {
        return max(0, $this->totalRecordedEvents - count($this->events));
    }

    public function getTotalDroppedLinks(): int
    {
        return max(0, $this->span->getTotalRecordedLinks() - count($this->links));
    }

    public function getStatus(): StatusData
    {
        return $this->status;
    }

    public function hasEnded(): bool
    {
        return $this->hasEnded;
    }
}
