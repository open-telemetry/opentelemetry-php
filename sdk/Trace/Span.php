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
     * @var API\Events
     * @readonly
     *
     * @todo: Java just has this as list<API\Event>, could we just do that?
     */
    private $events;

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

    /** @var int */
    private $totalRecordedEvents = 0;

    /** @var string */
    private $status = API\StatusCode::STATUS_UNSET;

    /** @var int */
    private $endEpochNanos;

    /** @var bool */
    private $hasEnded = false;

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

    /** @inheritDoc */
    public function getContext(): SpanContext
    {
        return $this->context;
    }

    /** @inheritDoc */
    public function isRecording(): bool
    {
        return !$this->hasEnded;
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

    /** @inheritDoc */
    public function updateName(string $name): API\Span
    {
        if ($this->hasEnded) {
            return $this;
        }
        $this->name = $name;

        return $this;
    }

    /** @inheritDoc */
    public function setStatus(string $code, string $description = null): API\Span
    {
        // TODO: Implement setStatus() method.
    }

    /** @inheritDoc */
    public function end(int $endEpochNanos = null): void
    {
        if ($this->hasEnded) {
            return;
        }

        $this->endEpochNanos = $endEpochNanos;
        $this->hasEnded = true;

        $this->spanProcessor->onEnd($this);
    }

    /** @inheritDoc */
    public function getName(): string
    {
        return $this->name;
    }

    public function getParentContext(): ?API\SpanContext
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
}
