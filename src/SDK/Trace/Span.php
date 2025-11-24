<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesBuilderInterface;
use OpenTelemetry\SDK\Common\Dev\Compatibility\Util as BcUtil;
use OpenTelemetry\SDK\Common\Exception\StackTraceFormatter;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppression;
use Throwable;

final class Span extends API\Span implements ReadWriteSpanInterface
{
    use LogsMessagesTrait;

    /** @var list<EventInterface> */
    private array $events = [];
    private int $totalRecordedEvents = 0;
    private StatusDataInterface $status;
    private ?int $endEpochNanos = null;
    private bool $hasEnded = false;

    /**
     * @param non-empty-string $name
     * @param list<LinkInterface> $links
     */
    private function __construct(
        private string $name,
        private readonly API\SpanContextInterface $context,
        private readonly InstrumentationScopeInterface $instrumentationScope,
        private readonly int $kind,
        private readonly API\SpanContextInterface $parentSpanContext,
        private readonly SpanLimits $spanLimits,
        private readonly SpanProcessorInterface $spanProcessor,
        private readonly ResourceInfo $resource,
        private AttributesBuilderInterface $attributesBuilder,
        private array $links,
        private int $totalRecordedLinks,
        private readonly int $startEpochNanos,
        private readonly SpanSuppression $spanSuppression,
    ) {
        $this->status = StatusData::unset();
    }

    /**
     * This method _MUST_ not be used directly.
     * End users should use a {@see API\TracerInterface} in order to create spans.
     *
     * @param non-empty-string $name
     * @param list<LinkInterface> $links
     * @psalm-param API\SpanKind::KIND_* $kind
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
        ContextInterface $parentContext,
        SpanLimits $spanLimits,
        SpanProcessorInterface $spanProcessor,
        ResourceInfo $resource,
        AttributesBuilderInterface $attributesBuilder,
        array $links,
        int $totalRecordedLinks,
        int $startEpochNanos,
        SpanSuppression $spanSuppression = new NoopSuppression(),
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
            $attributesBuilder,
            $links,
            $totalRecordedLinks,
            $startEpochNanos !== 0 ? $startEpochNanos : Clock::getDefault()->now(),
            $spanSuppression,
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
    public static function formatStackTrace(Throwable $e, ?array &$seen = null): string
    {
        BcUtil::triggerMethodDeprecationNotice(
            __METHOD__,
            'format',
            StackTraceFormatter::class
        );

        return StackTraceFormatter::format($e);
    }

    #[\Override]
    public function storeInContext(ContextInterface $context): ContextInterface
    {
        return $this->spanSuppression->suppress(parent::storeInContext($context));
    }

    /** @inheritDoc */
    #[\Override]
    public function getContext(): API\SpanContextInterface
    {
        return $this->context;
    }

    /** @inheritDoc */
    #[\Override]
    public function isRecording(): bool
    {
        return !$this->hasEnded;
    }

    /** @inheritDoc */
    #[\Override]
    public function setAttribute(string $key, $value): self
    {
        if ($this->hasEnded) {
            return $this;
        }

        $this->attributesBuilder[$key] = $value;

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function setAttributes(iterable $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->attributesBuilder[$key] = $value;
        }

        return $this;
    }

    #[\Override]
    public function addLink(SpanContextInterface $context, iterable $attributes = []): self
    {
        if ($this->hasEnded) {
            return $this;
        }
        if (!$context->isValid()) {
            return $this;
        }
        if (++$this->totalRecordedLinks > $this->spanLimits->getLinkCountLimit()) {
            return $this;
        }

        $this->links[] = new Link(
            $context,
            $this->spanLimits
                ->getLinkAttributesFactory()
                ->builder($attributes)
                ->build(),
        );

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function addEvent(string $name, iterable $attributes = [], ?int $timestamp = null): self
    {
        if ($this->hasEnded) {
            return $this;
        }
        if (++$this->totalRecordedEvents > $this->spanLimits->getEventCountLimit()) {
            return $this;
        }

        $timestamp ??= Clock::getDefault()->now();
        $eventAttributesBuilder = $this->spanLimits->getEventAttributesFactory()->builder($attributes);

        $this->events[] = new Event($name, $timestamp, $eventAttributesBuilder->build());

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function recordException(Throwable $exception, iterable $attributes = [], ?int $timestamp = null): self
    {
        if ($this->hasEnded) {
            return $this;
        }
        if (++$this->totalRecordedEvents > $this->spanLimits->getEventCountLimit()) {
            return $this;
        }

        $timestamp ??= Clock::getDefault()->now();
        $eventAttributesBuilder = $this->spanLimits->getEventAttributesFactory()->builder([
            'exception.type' => $exception::class,
            'exception.message' => $exception->getMessage(),
            'exception.stacktrace' => StackTraceFormatter::format($exception),
        ]);

        foreach ($attributes as $key => $value) {
            $eventAttributesBuilder[$key] = $value;
        }

        $this->events[] = new Event('exception', $timestamp, $eventAttributesBuilder->build());

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function updateName(string $name): self
    {
        if ($this->hasEnded) {
            return $this;
        }
        $this->name = $name;

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function setStatus(string $code, ?string $description = null): self
    {
        if ($this->hasEnded) {
            return $this;
        }

        // An attempt to set value Unset SHOULD be ignored.
        if ($code === API\StatusCode::STATUS_UNSET) {
            return $this;
        }

        // When span status is set to Ok it SHOULD be considered final and any further attempts to change it SHOULD be ignored.
        if ($this->status->getCode() === API\StatusCode::STATUS_OK) {
            return $this;
        }
        $this->status = StatusData::create($code, $description);

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function end(?int $endEpochNanos = null): void
    {
        if ($this->endEpochNanos !== null) {
            return;
        }

        $this->endEpochNanos = $endEpochNanos ?? Clock::getDefault()->now();
        $span = clone $this;
        $this->hasEnded = true; // prevent further modifications to the span by async code
        if ($this->spanProcessor instanceof ExtendedSpanProcessorInterface) {
            $this->spanProcessor->onEnding($span);
        }
        $span->hasEnded = true;

        $this->spanProcessor->onEnd($span);
        $span->checkForDroppedElements();
    }

    /** @inheritDoc */
    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function getParentContext(): API\SpanContextInterface
    {
        return $this->parentSpanContext;
    }

    #[\Override]
    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->instrumentationScope;
    }

    #[\Override]
    public function hasEnded(): bool
    {
        return $this->hasEnded;
    }

    #[\Override]
    public function toSpanData(): SpanDataInterface
    {
        return new ImmutableSpan(
            $this,
            $this->name,
            $this->links,
            $this->events,
            $this->attributesBuilder->build(),
            $this->totalRecordedLinks,
            $this->totalRecordedEvents,
            $this->status,
            $this->endEpochNanos ?? 0,
            $this->hasEnded
        );
    }

    /** @inheritDoc */
    #[\Override]
    public function getDuration(): int
    {
        return ($this->hasEnded ? $this->endEpochNanos : Clock::getDefault()->now()) - $this->startEpochNanos;
    }

    /** @inheritDoc */
    #[\Override]
    public function getKind(): int
    {
        return $this->kind;
    }

    /** @inheritDoc */
    #[\Override]
    public function getAttribute(string $key)
    {
        return $this->attributesBuilder[$key];
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

    private function checkForDroppedElements(): void
    {
        $spanData = $this->toSpanData(); //@todo could be optimized to reduce overhead of multiple calls
        $droppedLinkAttributes = 0;
        $droppedEventAttributes = 0;
        array_map(function (EventInterface $event) use (&$droppedEventAttributes) {
            $droppedEventAttributes += $event->getAttributes()->getDroppedAttributesCount();
        }, $spanData->getEvents());
        array_map(function (LinkInterface $link) use (&$droppedLinkAttributes) {
            $droppedLinkAttributes += $link->getAttributes()->getDroppedAttributesCount();
        }, $spanData->getLinks());
        if (
            $spanData->getTotalDroppedLinks() ||
            $spanData->getTotalDroppedEvents() ||
            $spanData->getAttributes()->getDroppedAttributesCount() ||
            $droppedEventAttributes ||
            $droppedLinkAttributes
        ) {
            self::logWarning('Dropped span attributes, links or events', [
                'trace_id' => $spanData->getTraceId(),
                'span_id' => $spanData->getSpanId(),
                'attributes' => $spanData->getAttributes()->getDroppedAttributesCount(),
                'links' => $spanData->getTotalDroppedLinks(),
                'link_attributes' => $droppedLinkAttributes,
                'events' => $spanData->getTotalDroppedEvents(),
                'event_attributes' => $droppedEventAttributes,
            ]);
        }
    }
}
