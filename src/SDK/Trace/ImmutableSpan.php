<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function max;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

/**
 * @psalm-immutable
 */
final class ImmutableSpan implements SpanDataInterface
{
    private Span $span;

    /** @var non-empty-string */
    private string $name;

    /** @var list<EventInterface> */
    private array $events;

    /** @var list<LinkInterface> */
    private array $links;

    private AttributesInterface $attributes;
    private int $totalRecordedEvents;
    private StatusDataInterface $status;
    private int $endEpochNanos;
    private bool $hasEnded;

    /**
     * @param non-empty-string $name
     * @param list<LinkInterface> $links
     * @param list<EventInterface> $events
     */
    public function __construct(
        Span $span,
        string $name,
        array $links,
        array $events,
        AttributesInterface $attributes,
        int $totalRecordedEvents,
        StatusDataInterface $status,
        int $endEpochNanos,
        bool $hasEnded
    ) {
        $this->span = $span;
        $this->name = $name;
        $this->links = $links;
        $this->events = $events;
        $this->attributes = $attributes;
        $this->totalRecordedEvents = $totalRecordedEvents;
        $this->status = $status;
        $this->endEpochNanos = $endEpochNanos;
        $this->hasEnded = $hasEnded;
    }

    public function getKind(): int
    {
        return $this->span->getKind();
    }

    public function getContext(): API\SpanContextInterface
    {
        return $this->span->getContext();
    }

    public function getParentContext(): API\SpanContextInterface
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

    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->span->getInstrumentationScope();
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

    public function getAttributes(): AttributesInterface
    {
        return $this->attributes;
    }

    public function getTotalDroppedEvents(): int
    {
        return max(0, $this->totalRecordedEvents - count($this->events));
    }

    public function getTotalDroppedLinks(): int
    {
        return max(0, $this->span->getTotalRecordedLinks() - count($this->links));
    }

    public function getStatus(): StatusDataInterface
    {
        return $this->status;
    }

    public function hasEnded(): bool
    {
        return $this->hasEnded;
    }
}
