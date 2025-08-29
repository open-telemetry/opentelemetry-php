final <?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Util;

use function count;
use function max;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesBuilderInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
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

    private AttributesBuilderInterface $attributesBuilder;
    private int $kind = API\SpanKind::KIND_INTERNAL;
    private StatusData $status;
    private ResourceInfo $resource;
    private InstrumentationScope $instrumentationScope;
    private API\SpanContextInterface $context;
    private API\SpanContextInterface $parentContext;
    private int $totalRecordedEvents = 0;
    private int $totalRecordedLinks = 0;
    private int $startEpochNanos = 1505855794194009601;
    private int $endEpochNanos = 1505855799465726528;
    private bool $hasEnded = false;

    public function __construct()
    {
        $this->attributesBuilder = Attributes::factory()->builder();
        $this->status = StatusData::unset();
        $this->resource = ResourceInfoFactory::emptyResource();
        $this->instrumentationScope = new InstrumentationScope('', null, null, Attributes::create([]));
        $this->context = API\SpanContext::getInvalid();
        $this->parentContext = API\SpanContext::getInvalid();
    }

    #[\Override]
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
    #[\Override]
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
    #[\Override]
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

    public function addEvent(string $name, AttributesInterface $attributes, ?int $timestamp = null): self
    {
        $this->events[] = new SDK\Event($name, $timestamp ?? Clock::getDefault()->now(), $attributes);

        return $this;
    }

    #[\Override]
    public function getAttributes(): AttributesInterface
    {
        return $this->attributesBuilder->build();
    }

    /**
     * @param (bool|float|int|string)[]|bool|int|string $value
     *
     * @psalm-param 0|1024|bool|list{0: 'string-1'|1|true, 1: 'string-2'|2|true, 2?: 3|false, 3?: float|true, 4?: 42}|string $value
     */
    public function addAttribute(string $key, array|string|int|bool $value): self
    {
        $this->attributesBuilder[$key] = $value;

        return $this;
    }

    #[\Override]
    public function getTotalDroppedEvents(): int
    {
        return max(0, $this->totalRecordedEvents - count($this->events));
    }

    public function setTotalRecordedEvents(int $totalRecordedEvents): self
    {
        $this->totalRecordedEvents = $totalRecordedEvents;

        return $this;
    }

    #[\Override]
    public function getTotalDroppedLinks(): int
    {
        return max(0, $this->totalRecordedLinks - count($this->links));
    }

    public function setTotalRecordedLinks(int $totalRecordedLinks): self
    {
        $this->totalRecordedLinks = $totalRecordedLinks;

        return $this;
    }

    #[\Override]
    public function getKind(): int
    {
        return $this->kind;
    }

    public function setKind(int $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    #[\Override]
    public function getStatus(): StatusData
    {
        return $this->status;
    }

    public function setStatus(StatusData $status): self
    {
        $this->status = $status;

        return $this;
    }

    #[\Override]
    public function getEndEpochNanos(): int
    {
        return $this->endEpochNanos;
    }

    public function setEndEpochNanos(int $endEpochNanos): self
    {
        $this->endEpochNanos = $endEpochNanos;

        return $this;
    }

    #[\Override]
    public function getStartEpochNanos(): int
    {
        return $this->startEpochNanos;
    }

    public function setStartEpochNanos(int $startEpochNanos): self
    {
        $this->startEpochNanos = $startEpochNanos;

        return $this;
    }

    #[\Override]
    public function hasEnded(): bool
    {
        return $this->hasEnded;
    }

    public function setHasEnded(bool $hasEnded): self
    {
        $this->hasEnded = $hasEnded;

        return $this;
    }

    #[\Override]
    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    public function setResource(ResourceInfo $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    #[\Override]
    public function getInstrumentationScope(): InstrumentationScope
    {
        return $this->instrumentationScope;
    }

    public function setInstrumentationScope(InstrumentationScope $instrumentationScope): self
    {
        $this->instrumentationScope = $instrumentationScope;

        return $this;
    }

    #[\Override]
    public function getContext(): API\SpanContextInterface
    {
        return $this->context;
    }

    public function setContext(API\SpanContextInterface $context): self
    {
        $this->context = $context;

        return $this;
    }

    #[\Override]
    public function getParentContext(): API\SpanContextInterface
    {
        return $this->parentContext;
    }

    public function setParentContext(API\SpanContextInterface $parentContext): self
    {
        $this->parentContext = $parentContext;

        return $this;
    }

    #[\Override]
    public function getTraceId(): string
    {
        return $this->getContext()->getTraceId();
    }

    #[\Override]
    public function getSpanId(): string
    {
        return $this->getContext()->getSpanId();
    }

    #[\Override]
    public function getParentSpanId(): string
    {
        return $this->getParentContext()->getSpanId();
    }
}
