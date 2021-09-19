<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Test;

use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace as SDK;
use OpenTelemetry\Sdk\Trace\StatusData;
use OpenTelemetry\Trace as API;

class SpanData implements SDK\SpanData
{
    /** @var non-empty-string */
    private string $name = 'test-span-data';

    private API\Links $links;
    private API\Events $events;
    private API\Attributes $attributes;
    private int $kind;
    private StatusData $status;
    private ResourceInfo $resource;
    private InstrumentationLibrary $instrumentationLibrary;
    private API\SpanContext $context;
    private API\SpanContext $parentContext;
    private int $totalAttributeCount = 0;
    private int $totalRecordedEvents = 0;
    private int $totalRecordedLinks = 0;
    private int $startEpochNanos = 1505855794194009601;
    private int $endEpochNanos = 1505855799465726528;
    private bool $hasEnded = false;

    public function __construct()
    {
        $this->links = new SDK\Links();
        $this->attributes = new SDK\Attributes();
        $this->events = new SDK\Events();
        $this->kind = API\SpanKind::KIND_INTERNAL;
        $this->status = StatusData::unset();
        $this->resource = ResourceInfo::emptyResource();
        $this->instrumentationLibrary = InstrumentationLibrary::getEmpty();
        $this->context = SDK\SpanContext::getInvalid();
        $this->parentContext = SDK\SpanContext::getInvalid();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @param non-empty-string $name */
    public function setName(string $name): SpanData
    {
        $this->name = $name;

        return $this;
    }

    public function getLinks(): API\Links
    {
        return $this->links;
    }

    public function setLinks(API\Links $links)
    {
        $this->links = $links;

        return $this;
    }

    public function getEvents(): API\Events
    {
        return $this->events;
    }

    public function setEvents(API\Events $events)
    {
        $this->events = $events;

        return $this;
    }

    public function addEvent(string $name, ?API\Attributes $attributes, int $timestamp = null)
    {
        $this->events->addEvent($name, $attributes, $timestamp);

        return $this;
    }

    public function getAttributes(): API\Attributes
    {
        return $this->attributes;
    }

    public function setAttributes(API\Attributes $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function addAttribute(string $key, $value)
    {
        $this->attributes->setAttribute($key, $value);

        return $this;
    }

    public function getTotalAttributeCount(): int
    {
        return $this->totalAttributeCount;
    }

    public function setTotalAttributeCount(int $totalAttributeCount): SpanData
    {
        $this->totalAttributeCount = $totalAttributeCount;

        return $this;
    }

    public function getTotalRecordedEvents(): int
    {
        return $this->totalRecordedEvents;
    }

    public function setTotalRecordedEvents(int $totalRecordedEvents): SpanData
    {
        $this->totalRecordedEvents = $totalRecordedEvents;

        return $this;
    }

    public function getTotalRecordedLinks(): int
    {
        return $this->totalRecordedLinks;
    }

    public function setTotalRecordedLinks(int $totalRecordedLinks): SpanData
    {
        $this->totalRecordedLinks = $totalRecordedLinks;

        return $this;
    }

    public function getKind(): int
    {
        return $this->kind;
    }

    public function setKind(int $kind): SpanData
    {
        $this->kind = $kind;

        return $this;
    }

    public function getStatus(): StatusData
    {
        return $this->status;
    }

    public function setStatus(StatusData $status): SpanData
    {
        $this->status = $status;

        return $this;
    }

    public function getEndEpochNanos(): int
    {
        return $this->endEpochNanos;
    }

    public function setEndEpochNanos(int $endEpochNanos): SpanData
    {
        $this->endEpochNanos = $endEpochNanos;

        return $this;
    }

    public function getStartEpochNanos(): int
    {
        return $this->startEpochNanos;
    }

    public function setStartEpochNanos(int $startEpochNanos): SpanData
    {
        $this->startEpochNanos = $startEpochNanos;

        return $this;
    }

    public function isHasEnded(): bool
    {
        return $this->hasEnded;
    }

    public function setHasEnded(bool $hasEnded): SpanData
    {
        $this->hasEnded = $hasEnded;

        return $this;
    }

    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    public function setResource(ResourceInfo $resource): SpanData
    {
        $this->resource = $resource;

        return $this;
    }

    public function getInstrumentationLibrary(): InstrumentationLibrary
    {
        return $this->instrumentationLibrary;
    }

    public function setInstrumentationLibrary(InstrumentationLibrary $instrumentationLibrary): SpanData
    {
        $this->instrumentationLibrary = $instrumentationLibrary;

        return $this;
    }

    public function getContext(): API\SpanContext
    {
        return $this->context;
    }

    public function setContext(API\SpanContext $context): SpanData
    {
        $this->context = $context;

        return $this;
    }

    public function getParentContext(): API\SpanContext
    {
        return $this->parentContext;
    }

    public function setParentContext(API\SpanContext $parentContext): SpanData
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
