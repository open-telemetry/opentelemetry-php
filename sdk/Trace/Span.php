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
use OpenTelemetry\Trace\Attributes;
use OpenTelemetry\Trace\SpanContext;
use function sprintf;
use function str_replace;
use Throwable;

class Span implements ReadWriteSpan
{
    /** @var NoopSpan|null */
    private static $invalidSpan;

    /**
     * This method _MUST_ not be used directly.
     * End users should use a {@see Tracer} in order to create spans.
     *
     * @param API\SpanKind::KIND_* $kind
     *
     * @internal
     */
    public static function startSpan(
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
    ): API\Span {
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

    /**
     * @var API\SpanContext
     * @readonly
     */
    private $context;

    /**
     * @var API\SpanContext
     * @readonly
     */
    private $parentSpanContext;

    /**
     * @var SpanProcessor
     * @readonly
     */
    private $spanProcessor;

    /**
     * @var API\Links|null
     * @readonly
     *
     * @todo: Java just has this as list<API\Link>, could we just do that?
     */
    private $links;

    /**
     * @var int
     * @readonly
     */
    private $totalRecordedLinks;

    /**
     * @var int
     * @readonly
     */
    private $kind;

    // TODO: Store a clock instance on the Span?

    /**
     * @var ResourceInfo
     * @readonly
     */
    private $resource;

    /**
     * @var InstrumentationLibrary
     * @readonly
     */
    private $instrumentationLibrary;

    /**
     * @var int
     * @readonly
     */
    private $startEpochNanos;

    /** @var non-empty-string */
    private $name;

    /** @var API\Attributes|null */
    private $attributes;

    /**
     * @var API\Events
     *
     * @todo: Java just has this as list<API\Event>, could we just do that?
     */
    private $events;

    /** @var int */
    private $totalRecordedEvents = 0;

    /** @var string */
    private $status = API\StatusCode::STATUS_UNSET;

    /** @var int */
    private $endEpochNanos;

    /** @var bool */
    private $ended = false;

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
    }

    public function getContext(): SpanContext
    {
        // TODO: Implement getContext() method.
    }

    public function isRecording(): bool
    {
        // TODO: Implement isRecording() method.
    }

    public function setAttribute(string $key, $value): API\Span
    {
        // TODO: Implement setAttribute() method.
    }

    public function setAttributes(Attributes $attributes): API\Span
    {
        // TODO: Implement setAttributes() method.
    }

    public function addEvent(string $name, ?Attributes $attributes = null, int $timestamp = null): API\Span
    {
        // TODO: Implement addEvent() method.
    }

    public function recordException(Throwable $exception, ?Attributes $attributes = null): API\Span
    {
        // TODO: Implement recordException() method.
    }

    public function updateName(string $name): API\Span
    {
        // TODO: Implement updateName() method.
    }

    public function setStatus(string $code, string $description = null): API\Span
    {
        // TODO: Implement setStatus() method.
    }

    public function end(int $timestamp = null): API\Span
    {
        // TODO: Implement end() method.
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function getParentContext(): ?API\SpanContext
    {
        // TODO: Implement getParentContext() method.
    }

    public function getInstrumentationLibrary(): InstrumentationLibrary
    {
        // TODO: Implement getInstrumentationLibrary() method.
    }

    public function ended(): bool
    {
        // TODO: Implement ended() method.
    }

    public function toSpanData()
    {
        // TODO: Implement toSpanData() method.
    }

    public function getDuration(): int
    {
        // TODO: Implement getDuration() method.
    }

    /** @inheritDoc */
    public function getKind(): int
    {
        // TODO: Implement getKind() method.
    }

    public function getAttribute(string $key)
    {
        // TODO: Implement getAttribute() method.
    }

//    public function __construct(
//        string $name,
//        API\SpanContext $spanContext,
//        ?API\SpanContext $parentSpanContext = null,
//        ?ResourceInfo $resource = null,
//        int $spanKind = API\SpanKind::KIND_INTERNAL,
//        ?API\Attributes $attributes = null,
//        ?API\Links $links = null,
//        ?SpanProcessor $spanProcessor = null
//    ) {
//        $this->name = $name;
//        $this->spanContext = $spanContext;
//        $this->parentSpanContext = $parentSpanContext;
//        $this->spanKind = $spanKind;
//        $this->resource =  $resource ?? ResourceInfo::emptyResource();
//        $this->spanProcessor = $spanProcessor;
//        [$this->startEpochTimestamp, $this->start] = Clock::get()->moment();
//        $this->spanStatus = new SpanStatus();
//
//        // todo: set these to null until needed
//        $this->attributes = $attributes ?? new Attributes();
//        $this->events = new Events();
//        $this->links = $links ?? new Links();
//    }
//
//    /**
//     * @internal
//     */
//    public function setInstrumentationLibrary(InstrumentationLibrary $instrumentationLibrary)
//    {
//        $this->instrumentationLibrary = $instrumentationLibrary;
//    }
//
//    /**
//     * @internal
//     */
//    public function getInstrumentationLibrary(): InstrumentationLibrary
//    {
//        return $this->instrumentationLibrary;
//    }
//
//    public function getResource(): ResourceInfo
//    {
//        return clone $this->resource;
//    }
//
//    public function getParentContext(): ?API\SpanContext
//    {
//        // todo: Spec says a parent is a Span, SpanContext, or null -> should we implement this here?
//        return $this->parentSpanContext !== null ? clone $this->parentSpanContext : null;
//    }
//
//    /** @inheritDoc */
//    public function setStatus(string $code, ?string $description = null): API\Span
//    {
//        if ($this->isRecording()) {
//            $this->spanStatus->setStatus($code, $description);
//        }
//
//        return $this;
//    }
//
//    public function getStart(): int
//    {
//        return $this->start;
//    }
//
//    public function setStart(int $start): Span
//    {
//        $this->start = $start;
//
//        return $this;
//    }
//
//    /**
//     * @param int|null $timestamp
//     * @return Span
//     */
//    public function end(?int $timestamp = null): API\Span
//    {
//        if (!isset($this->end)) {
//            $this->end = $timestamp ?? Clock::get()->now();
//            $this->ended = true;
//        }
//
//        if ($this->spanProcessor !== null) {
//            $this->spanProcessor->onEnd($this);
//        }
//
//        return $this;
//    }
//    public function ended(): bool
//    {
//        return $this->ended;
//    }
//    public function setStartEpochTimestamp(int $timestamp): Span
//    {
//        $this->startEpochTimestamp = $timestamp;
//
//        return $this;
//    }
//
//    public function getStartEpochTimestamp(): int
//    {
//        return $this->startEpochTimestamp;
//    }
//
//    public function getEndEpochTimestamp(): ?int
//    {
//        return $this->endEpochTimestamp;
//    }
//
//    public function getEnd(): ?int
//    {
//        return $this->end;
//    }
//
//    public function getStatus(): API\SpanStatus
//    {
//        return $this->spanStatus;
//    }
//
//    public function isRecording(): bool
//    {
//        return null === $this->end;
//    }
//
//    public function getDuration(): ?int
//    {
//        if (!$this->end) {
//            return null;
//        }
//
//        return ($this->end - $this->start);
//    }
//
//    public function getName(): string
//    {
//        return $this->name;
//    }
//
//    /**
//     * @param string $name
//     * @return Span
//     */
//    public function updateName(string $name): API\Span
//    {
//        $this->name = $name;
//
//        return $this;
//    }
//
//    public function getContext(): API\SpanContext
//    {
//        return $this->spanContext;
//    }
//
//    public function getAttribute(string $key): ?Attribute
//    {
//        return $this->attributes->getAttribute($key);
//    }
//
//    /**
//     * @param string $key
//     * @param array|bool|float|int|string $value
//     * @return Span
//     */
//    public function setAttribute(string $key, $value): API\Span
//    {
//        if ($this->isRecording()) {
//            $this->attributes->setAttribute($key, $value);
//        }
//
//        return $this;
//    }
//
//    public function getAttributes(): API\Attributes
//    {
//        return $this->attributes;
//    }
//
//    public function replaceAttributes(API\Attributes $attributes): self
//    {
//        if ($this->isRecording()) {
//            $this->attributes = $attributes;
//        }
//
//        return $this;
//    }
//
//    // todo: is accepting an Iterator enough to satisfy AddLazyEvent?  -> Looks like the spec might have been updated here: https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/api-tracing.md#add-events
//    /**
//     * @param string $name
//     * @param API\Attributes|null $attributes
//     * @param int|null $timestamp
//     * @return Span
//     */
//    public function addEvent(string $name, ?API\Attributes $attributes = null, int $timestamp = null): API\Span
//    {
//        if ($this->isRecording()) {
//            $this->events->addEvent($name, $attributes, $timestamp);
//        }
//
//        return $this;
//    }
//
//    /**
//     * @param Throwable $exception
//     * @param API\Attributes|null $attributes
//     * @return Span
//     */
//    public function recordException(Throwable $exception, ?API\Attributes $attributes = null): API\Span
//    {
//        $eventAttributes = new Attributes(
//            [
//                'exception.type' => get_class($exception),
//                'exception.message' => $exception->getMessage(),
//                'exception.stacktrace' => self::getStackTrace($exception),
//            ]
//        );
//        foreach ($attributes ?? [] as $attribute) {
//            $eventAttributes->setAttribute($attribute->getKey(), $attribute->getValue());
//        }
//
//        return $this->addEvent('exception', $eventAttributes, Clock::get()->timestamp());
//    }
//
//    public function getEvents(): API\Events
//    {
//        return $this->events;
//    }
//
//    /* A Span is said to have a remote parent if it is the child of a Span
//     * created in another process. Each propagators' deserialization must
//     * set IsRemote to true on a parent
//     * SpanContext so Span creation knows if the parent is remote.
//     * Returns true if the SpanContext was propagated from a
//     * remote parent. When creating children
//     * from remote spans, their IsRemote flag MUST be set to false.
//    */
//    public function isRemote(): bool
//    {
//        return $this->spanContext->isRemote();
//    }
//
//    public function setLinks(API\Links $links): Span
//    {
//        $this->links = $links;
//
//        return $this;
//    }
//
//    public function getLinks(): API\Links
//    {
//        return $this->links;
//    }
//
//    public function getKind(): int
//    {
//        return $this->spanKind;
//    }
//
//    public function getCanonicalStatusCode(): string
//    {
//        return $this->spanStatus->getCanonicalStatusCode();
//    }
//
//    public function getStatusDescription(): string
//    {
//        return $this->spanStatus->getStatusDescription();
//    }
//
//    public function isStatusOk(): bool
//    {
//        return $this->spanStatus->isStatusOK();
//    }
//
//    /** @inheritDoc */
//    public function storeInContext(Context $context): Context
//    {
//        return $context->with(SpanContextKey::instance(), $this);
//    }
//
//    /** @inheritDoc */
//    public function activate(): Scope
//    {
//        return Context::getCurrent()->withContextValue($this)->activate();
//    }
//
}
