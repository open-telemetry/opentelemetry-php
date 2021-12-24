<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function array_key_exists;
use function array_shift;
use function basename;
use function count;
use function ctype_space;
use function get_class;
use function in_array;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\AttributeLimits;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use function sprintf;
use function str_replace;
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
    private InstrumentationLibrary $instrumentationLibrary;

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
        InstrumentationLibrary $instrumentationLibrary,
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
        $this->instrumentationLibrary = $instrumentationLibrary;
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
        InstrumentationLibrary $instrumentationLibrary,
        int $kind,
        API\SpanInterface $parentSpan,
        Context $parentContext,
        SpanLimits $spanLimits,
        SpanProcessorInterface $spanProcessor,
        ResourceInfo $resource,
        ?AttributesInterface $attributes,
        array $links,
        int $totalRecordedLinks,
        int $userStartEpochNanos
    ): self {
        if (0 !== $userStartEpochNanos) {
            $startEpochNanos = $userStartEpochNanos;
        } else {
            $startEpochNanos = AbstractClock::getDefault()->now();
        }

        $span = new self(
            $name,
            $context,
            $instrumentationLibrary,
            $kind,
            $parentSpan->getContext(),
            $spanLimits,
            $spanProcessor,
            $resource,
            $attributes,
            $links,
            $totalRecordedLinks,
            $startEpochNanos
        );

        // Call onStart here to ensure the span is fully initialized.
        $spanProcessor->onStart($span, $parentContext);

        return $span;
    }

    /**
     * This function provides a more java-like stacktrace
     * that supports exception chaining and provides exact
     * lines of where exceptions are thrown
     *
     * Example:
     * Exception: Thrown from grandparent
     *  at grandparent_func(test.php:56)
     *  at parent_func(test.php:51)
     *  at child_func(test.php:44)
     *  at (main)(test.php:62)
     *
     * Credit: https://www.php.net/manual/en/exception.gettraceasstring.php#114980
     */
    public static function formatStackTrace(Throwable $e, array &$seen = null): string
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = [];
        if (!$seen) {
            $seen = [];
        }
        $trace  = $e->getTrace();
        $prev   = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (in_array($current, $seen, true)) {
                $result[] = sprintf(' ... %d more', count($trace)+1);

                break;
            }
            $result[] = sprintf(
                ' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : 'main',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line ?? ''
            );
            $seen[] = "$file:$line";
            if (!count($trace)) {
                break;
            }
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
        $result = implode("\n", $result);
        if ($prev) {
            $result  .= "\n" . self::formatStackTrace($prev, $seen);
        }

        return $result;
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
                $timestamp ?? AbstractClock::getDefault()->now(),
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
        $timestamp = AbstractClock::getDefault()->now();
        $eventAttributes = new Attributes([
                'exception.type' => get_class($exception),
                'exception.message' => $exception->getMessage(),
                'exception.stacktrace' => self::formatStackTrace($exception),
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

        $this->endEpochNanos = $endEpochNanos ?? AbstractClock::getDefault()->now();
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

    public function getInstrumentationLibrary(): InstrumentationLibrary
    {
        return $this->instrumentationLibrary;
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
        return ($this->hasEnded ? $this->endEpochNanos : AbstractClock::getDefault()->now()) - $this->startEpochNanos;
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
