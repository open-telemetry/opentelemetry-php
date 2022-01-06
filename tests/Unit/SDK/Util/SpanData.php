<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Util;

use function count;
use function max;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\AttributesBuilder;
use OpenTelemetry\SDK\AttributesBuilderInterface;
use OpenTelemetry\SDK\AttributesInterface;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace as SDK;
use OpenTelemetry\SDK\Trace\EventInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;
use OpenTelemetry\SDK\Trace\StatusData;

class SpanData implements SDK\SpanDataInterface
{
    /** @var non-empty-string */
    private string $name = 'test-span-data';

    /** @var list<EventInterface> */
    private array $events = [];

    /** @var list<LinkInterface>
     */
    private array $links = [];

    private AttributesBuilderInterface $attributes;
    private int $kind;
    private StatusData $status;
    private ResourceInfo $resource;
    private InstrumentationLibrary $instrumentationLibrary;
    private API\SpanContextInterface $context;
    private API\SpanContextInterface $parentContext;
    private int $totalRecordedEvents = 0;
    private int $totalRecordedLinks = 0;
    private int $startEpochNanos = 1505855794194009601;
    private int $endEpochNanos = 1505855799465726528;
    private bool $hasEnded = false;

    public function __construct()
    {
        $this->attributes = AttributesBuilder::from([]);
        $this->kind = API\SpanKind::KIND_INTERNAL;
        $this->status = StatusData::unset();
        $this->resource = ResourceInfo::emptyResource();
        $this->instrumentationLibrary = InstrumentationLibrary::getEmpty(); /** @phan-suppress-current-line PhanAccessMethodInternal */
        $this->context = API\SpanContext::getInvalid();
        $this->parentContext = API\SpanContext::getInvalid();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @param non-empty-string $name */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /** @inheritDoc */
    public function getLinks(): array
    {
        return $this->links;
    }

    /** @param list<LinkInterface> $links */
    public function setLinks(array $links): self
    {
        $this->links = $links;

        return $this;
    }

    public function addLink(API\SpanContextInterface $context, AttributesInterface $attributes): self
    {
        $this->links[] = new SDK\Link($context, $attributes);

        return $this;
    }

    /** @inheritDoc */
    public function getEvents(): array
    {
        return $this->events;
    }

    /** @param list<EventInterface> $events */
    public function setEvents(array $events): self
    {
        $this->events = $events;

        return $this;
    }

    public function addEvent(string $name, AttributesInterface $attributes, int $timestamp = null): self
    {
        $this->events[] = new SDK\Event($name, $timestamp ?? AbstractClock::getDefault()->now(), $attributes);

        return $this;
    }

    public function getAttributes(): AttributesInterface
    {
        return $this->attributes->build();
    }

    public function addAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function getTotalDroppedAttributes(): int
    {
        return $this->attributes->build()->getDroppedAttributesCount();
    }

    public function getTotalDroppedEvents(): int
    {
        return max(0, $this->totalRecordedEvents - count($this->events));
    }

    public function setTotalRecordedEvents(int $totalRecordedEvents): self
    {
        $this->totalRecordedEvents = $totalRecordedEvents;

        return $this;
    }

    public function getTotalDroppedLinks(): int
    {
        return max(0, $this->totalRecordedLinks - count($this->links));
    }

    public function setTotalRecordedLinks(int $totalRecordedLinks): self
    {
        $this->totalRecordedLinks = $totalRecordedLinks;

        return $this;
    }

    public function getKind(): int
    {
        return $this->kind;
    }

    public function setKind(int $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getStatus(): StatusData
    {
        return $this->status;
    }

    public function setStatus(StatusData $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEndEpochNanos(): int
    {
        return $this->endEpochNanos;
    }

    public function setEndEpochNanos(int $endEpochNanos): self
    {
        $this->endEpochNanos = $endEpochNanos;

        return $this;
    }

    public function getStartEpochNanos(): int
    {
        return $this->startEpochNanos;
    }

    public function setStartEpochNanos(int $startEpochNanos): self
    {
        $this->startEpochNanos = $startEpochNanos;

        return $this;
    }

    public function hasEnded(): bool
    {
        return $this->hasEnded;
    }

    public function setHasEnded(bool $hasEnded): self
    {
        $this->hasEnded = $hasEnded;

        return $this;
    }

    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    public function setResource(ResourceInfo $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getInstrumentationLibrary(): InstrumentationLibrary
    {
        return $this->instrumentationLibrary;
    }

    public function setInstrumentationLibrary(InstrumentationLibrary $instrumentationLibrary): self
    {
        $this->instrumentationLibrary = $instrumentationLibrary;

        return $this;
    }

    public function getContext(): API\SpanContextInterface
    {
        return $this->context;
    }

    public function setContext(API\SpanContextInterface $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getParentContext(): API\SpanContextInterface
    {
        return $this->parentContext;
    }

    public function setParentContext(API\SpanContextInterface $parentContext): self
    {
        $this->parentContext = $parentContext;

        return $this;
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
}
