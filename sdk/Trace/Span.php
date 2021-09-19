<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use function array_key_exists;
use function array_shift;
use function basename;
use function count;
use function get_class;
use function in_array;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Scope;
use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;
use function sprintf;
use function str_replace;
use Throwable;

class Span implements ReadWriteSpan
{
    private static ?NoopSpan $invalidSpan;

    /**
     * This method _MUST_ not be used directly.
     * End users should use a {@see Tracer} in order to create spans.
     *
     * @param non-empty-string $name
     * @param API\SpanKind::KIND_* $kind
     *
     * @internal
     * @psalm-internal OpenTelemetry\Sdk
     */
    public static function startSpan(
        string $name,
        API\SpanContext $context,
        InstrumentationLibrary $instrumentationLibrary,
        int $kind,
        API\Span $parentSpan,
        Context $parentContext,
        SpanProcessor $spanProcessor,
        ResourceInfo $resource,
        ?API\Attributes $attributes,
        API\Links $links,
        int $totalRecordedLinks,
        int $userStartEpochNanos
    ): API\Span {
        if (0 !== $userStartEpochNanos) {
            $startEpochNanos = $userStartEpochNanos;
        } else {
            $startEpochNanos = Clock::get()->now();
        }

        $span = new self(
            $name,
            $context,
            $instrumentationLibrary,
            $kind,
            $parentSpan->getContext(),
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

    /** @inheritDoc */
    public static function fromContext(Context $context): API\Span
    {
        if ($span = $context->get(SpanContextKey::instance())) {
            return $span;
        }

        return self::getInvalid();
    }

    /** @inheritDoc */
    public static function getCurrent(): API\Span
    {
        return self::fromContext(Context::getCurrent());
    }

    /** @inheritDoc */
    public static function getInvalid(): API\Span
    {
        if (null === self::$invalidSpan) {
            self::$invalidSpan = new NoopSpan();
        }

        return self::$invalidSpan;
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
    public static function wrap(API\SpanContext $spanContext): API\Span
    {
        return new NoopSpan($spanContext);
    }

    // TODO: Add a SpanLimits object to the constructor for configuration options.

    /** @readonly */
    private API\SpanContext $context;

    /** @readonly */
    private API\SpanContext $parentSpanContext;

    /** @readonly */
    private SpanProcessor $spanProcessor;

    /**
     * @readonly
     *
     * @todo: Java just has this as list<API\Event>, could we just do that?
     */
    private API\Events $events;

    /**
     * @readonly
     *
     * @todo: Java just has this as list<API\Link>, could we just do that?
     */
    private API\Links $links;

    /** @readonly */
    private int $totalRecordedLinks;

    /** @readonly */
    private int $kind;

    // TODO: Store a clock instance on the Span?

    /** @readonly */
    private ResourceInfo $resource;

    /** @readonly */
    private InstrumentationLibrary $instrumentationLibrary;

    /** @readonly */
    private int $startEpochNanos;

    /** @var non-empty-string */
    private string $name;

    private ?API\Attributes $attributes;
    private int $totalRecordedEvents = 0;
    private StatusData $status;
    private int $endEpochNanos;
    private bool $hasEnded = false;

    /** @param non-empty-string $name */
    private function __construct(
        string $name,
        API\SpanContext $context,
        InstrumentationLibrary $instrumentationLibrary,
        int $kind,
        API\SpanContext $parentSpanContext,
        SpanProcessor $spanProcessor,
        ResourceInfo $resource,
        ?API\Attributes $attributes,
        API\Links $links,
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
        $this->attributes = $attributes;
        $this->events = new Events();
        $this->status = StatusData::unset();
    }

    /** @inheritDoc */
    public function activate(): Scope
    {
        return Context::getCurrent()->withContextValue($this)->activate();
    }

    /** @inheritDoc */
    public function getContext(): API\SpanContext
    {
        return $this->context;
    }

    /** @inheritDoc */
    public function isRecording(): bool
    {
        return !$this->hasEnded;
    }

    /** @inheritDoc */
    public function setAttribute(string $key, $value): ReadWriteSpan
    {
        if (null === $value || $this->hasEnded) {
            return $this;
        }

        if (null === $this->attributes) {
            $this->attributes = new Attributes();
        }

        $this->attributes->setAttribute($key, $value);

        return $this;
    }

    /** @inheritDoc */
    public function setAttributes(API\Attributes $attributes): ReadWriteSpan
    {
        if (0 === $attributes->count()) {
            return $this;
        }

        foreach ($attributes as $attribute) {
            $this->setAttribute($attribute->getKey(), $attribute->getValue());
        }

        return $this;
    }

    /** @inheritDoc */
    public function addEvent(string $name, ?API\Attributes $attributes = null, int $timestamp = null): ReadWriteSpan
    {
        if ($this->hasEnded) {
            return $this;
        }

        $this->events->addEvent($name, $attributes, $timestamp);
        $this->totalRecordedEvents++;

        return $this;
    }

    /** @inheritDoc */
    public function recordException(Throwable $exception, API\Attributes $attributes = null): ReadWriteSpan
    {
        $timestamp = Clock::get()->now();
        $eventAttributes = new Attributes([
                'exception.type' => get_class($exception),
                'exception.message' => $exception->getMessage(),
                'exception.stacktrace' => self::formatStackTrace($exception),
            ]);

        if ($attributes) {
            foreach ($attributes as $attribute) {
                $eventAttributes->setAttribute($attribute->getKey(), $attribute->getValue());
            }
        }

        return $this->addEvent('exception', $eventAttributes, $timestamp);
    }

    /** @inheritDoc */
    public function updateName(string $name): ReadWriteSpan
    {
        if ($this->hasEnded) {
            return $this;
        }
        $this->name = $name;

        return $this;
    }

    /** @inheritDoc */
    public function setStatus(string $code, string $description = null): ReadWriteSpan
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

        $this->endEpochNanos = $endEpochNanos ?? Clock::get()->now();
        $this->hasEnded = true;

        $this->spanProcessor->onEnd($this);
    }

    /** @inheritDoc */
    public function getName(): string
    {
        return $this->name;
    }

    public function getParentContext(): API\SpanContext
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

    public function toSpanData(): SpanData
    {
        return new ImmutableSpan(
            $this,
            $this->name,
            $this->links,
            $this->getImmutableEvents(),
            $this->getImmutableAttributes(),
            (null === $this->attributes) ? 0 : $this->attributes->count(),
            $this->totalRecordedEvents,
            $this->status,
            $this->endEpochNanos,
            $this->hasEnded
        );
    }

    /** @inheritDoc */
    public function getDuration(): int
    {
        return ($this->hasEnded ? $this->endEpochNanos : Clock::get()->now()) - $this->startEpochNanos;
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

    /** @inheritDoc */
    public function storeInContext(Context $context): Context
    {
        return $context->with(SpanContextKey::instance(), $this);
    }

    private function getImmutableAttributes(): API\Attributes
    {
        if (null === $this->attributes) {
            return new Attributes();
        }

        return clone $this->attributes;
    }

    private function getImmutableEvents(): API\Events
    {
        return clone $this->events;
    }
}
