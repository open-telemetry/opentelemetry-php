<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function count;
use function ctype_space;
use function get_class;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributeLimits;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Dev\Compatibility\Util as BcUtil;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Common\Util\TracingUtil;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use Throwable;

final class Span extends API\AbstractSpan implements ReadWriteSpanInterface
{

    /** @readonly */
    private API\SpanContextInterface $context;

    /** @readonly */
    private API\SpanContextInterface $parentSpanContext;

    /** @readonly */
    private SpanLimits $spanLimits;

    /** @readonly */
    private SpanProcessorInterface $spanProcessor;

    /**
     * @readonly
     *
     * @var list<LinkInterface>
     */
    private array $links;

    /** @readonly */
    private int $totalRecordedLinks;

    /** @readonly */
    private int $kind;

    /** @readonly */
    private ResourceInfo $resource;

    /** @readonly */
    private InstrumentationScopeInterface $instrumentationScope;

    /** @readonly */
    private int $startEpochNanos;

    /** @var non-empty-string */
    private string $name;

    /** @var list<EventInterface> */
    private array $events = [];

    private ?AttributesInterface $attributes;
    private int $totalRecordedEvents = 0;
    private StatusDataInterface $status;
    private int $endEpochNanos = 0;
    private bool $hasEnded = false;

    /**
     * @param non-empty-string $name
     * @param list<LinkInterface> $links
     */
    private function __construct(
        string $name,
        API\SpanContextInterface $context,
        InstrumentationScopeInterface $instrumentationScope,
        int $kind,
        API\SpanContextInterface $parentSpanContext,
        SpanLimits $spanLimits,
        SpanProcessorInterface $spanProcessor,
        ResourceInfo $resource,
        ?AttributesInterface $attributes,
        array $links,
        int $totalRecordedLinks,
        int $startEpochNanos
    ) {
        $this->context = $context;
        $this->instrumentationScope = $instrumentationScope;
        $this->parentSpanContext = $parentSpanContext;
        $this->links = $links;
        $this->totalRecordedLinks = $totalRecordedLinks;
        $this->name = $name;
        $this->kind = $kind;
        $this->spanProcessor = $spanProcessor;
        $this->resource = $resource;
        $this->startEpochNanos = $startEpochNanos;
        $this->attributes = Attributes::withLimits($attributes ?? new Attributes(), $spanLimits->getAttributeLimits());
        $this->status = StatusData::unset();
        $this->spanLimits = $spanLimits;
    }

    /**
     * This method _MUST_ not be used directly.
     * End users should use a {@see API\TracerInterface} in order to create spans.
     *
     * @param non-empty-string $name
     * @psalm-param API\SpanKind::KIND_* $kind
     * @param list<LinkInterface> $links
     *
     * @internal
     * @psalm-internal OpenTelemetry
     */
    public static function startSpan(
        string $name,
        API\SpanContextInterface $context,
        InstrumentationScopeInterface $instrumentationScope,
        int $kind,
        API\SpanInterface $parentSpan,
        Context $parentContext,
        SpanLimits $spanLimits,
        SpanProcessorInterface $spanProcessor,
        ResourceInfo $resource,
        ?AttributesInterface $attributes,
        array $links,
        int $totalRecordedLinks,
        int $startEpochNanos
    ): self {
        $span = new self(
            $name,
            $context,
            $instrumentationScope,
            $kind,
            $parentSpan->getContext(),
            $spanLimits,
            $spanProcessor,
            $resource,
            $attributes,
            $links,
            $totalRecordedLinks,
            $startEpochNanos !== 0 ? $startEpochNanos : ClockFactory::getDefault()->now()
        );

        // Call onStart here to ensure the span is fully initialized.
        $spanProcessor->onStart($span, $parentContext);

        return $span;
    }

    /**
     * Backward compatibility methods
     *
     * @codeCoverageIgnore
     */
    public static function formatStackTrace(Throwable $e, array &$seen = null): string
    {
        BcUtil::triggerMethodDeprecationNotice(
            __METHOD__,
            'format',
            StackTraceFormatter::class
        );

        return StackTraceFormatter::format($e);
    }

    /** @inheritDoc */
    public function getContext(): API\SpanContextInterface
    {
        return $this->context;
    }

    /** @inheritDoc */
    public function isRecording(): bool
    {
        return !$this->hasEnded;
    }

    /** @inheritDoc */
    public function setAttribute(string $key, $value): self
    {
        if ($this->hasEnded || ctype_space($key)) {
            return $this;
        }

        if (null === $this->attributes) {
            $this->attributes = Attributes::withLimits(new Attributes(), $this->spanLimits->getAttributeLimits());
        }

        $this->attributes->setAttribute($key, $value);

        return $this;
    }

    /** @inheritDoc */
    public function setAttributes(iterable $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /** @inheritDoc */
    public function addEvent(string $name, iterable $attributes = [], int $timestamp = null): self
    {
        if ($this->hasEnded) {
            return $this;
        }

        if (count($this->events) < $this->spanLimits->getEventCountLimit()) {
            $this->events[] = new Event(
                $name,
                $timestamp ?? ClockFactory::getDefault()->now(),
                Attributes::withLimits(
                    $attributes,
                    new AttributeLimits(
                        $this->spanLimits->getAttributePerEventCountLimit(),
                        $this->spanLimits->getAttributeLimits()->getAttributeValueLengthLimit()
                    )
                ),
            );
        }

        $this->totalRecordedEvents++;

        return $this;
    }

    /** @inheritDoc */
    public function recordException(Throwable $exception, iterable $attributes = []): self
    {
        $timestamp = ClockFactory::getDefault()->now();
        $eventAttributes = new Attributes([
                'exception.type' => get_class($exception),
                'exception.message' => $exception->getMessage(),
                'exception.stacktrace' => StackTraceFormatter::format($exception),
            ]);

        foreach ($attributes as $key => $value) {
            $eventAttributes->setAttribute($key, $value);
        }

        return $this->addEvent('exception', $eventAttributes, $timestamp);
    }

    /** @inheritDoc */
    public function updateName(string $name): self
    {
        if ($this->hasEnded) {
            return $this;
        }
        $this->name = $name;

        return $this;
    }

    /** @inheritDoc */
    public function setStatus(string $code, string $description = null): self
    {
        if ($this->hasEnded) {
            return $this;
        }

        $this->status = StatusData::create($code, $description);

        return $this;
    }

    /** @inheritDoc */
    public function end(int $endEpochNanos = null): void
    {
        if ($this->hasEnded) {
            return;
        }

        $this->endEpochNanos = $endEpochNanos ?? ClockFactory::getDefault()->now();
        $this->hasEnded = true;

        $this->spanProcessor->onEnd($this);
    }

    /** @inheritDoc */
    public function getName(): string
    {
        return $this->name;
    }

    public function getParentContext(): API\SpanContextInterface
    {
        return $this->parentSpanContext;
    }

    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->instrumentationScope;
    }

    public function hasEnded(): bool
    {
        return $this->hasEnded;
    }

    public function toSpanData(): SpanDataInterface
    {
        return new ImmutableSpan(
            $this,
            $this->name,
            $this->links,
            $this->events,
            $this->getImmutableAttributes(),
            (null === $this->attributes) ? 0 : $this->attributes->getTotalAddedValues(),
            $this->totalRecordedEvents,
            $this->status,
            $this->endEpochNanos,
            $this->hasEnded
        );
    }

    /** @inheritDoc */
    public function getDuration(): int
    {
        return ($this->hasEnded ? $this->endEpochNanos : ClockFactory::getDefault()->now()) - $this->startEpochNanos;
    }

    /** @inheritDoc */
    public function getKind(): int
    {
        return $this->kind;
    }

    /** @inheritDoc */
    public function getAttribute(string $key)
    {
        if (null === $this->attributes) {
            return null;
        }

        return $this->attributes->get($key);
    }

    public function getStartEpochNanos(): int
    {
        return $this->startEpochNanos;
    }

    public function getTotalRecordedLinks(): int
    {
        return $this->totalRecordedLinks;
    }

    public function getTotalRecordedEvents(): int
    {
        return $this->totalRecordedEvents;
    }

    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    private function getImmutableAttributes(): AttributesInterface
    {
        if (null === $this->attributes) {
            return new Attributes();
        }

        return clone $this->attributes;
    }
}
