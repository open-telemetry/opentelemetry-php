<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Scope;
use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;
use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#wrapping-a-spancontext-in-a-span
 *
 * @todo: Implement this on the API side.
 * @todo: Can we just use {@see https://www.php.net/manual/en/language.oop5.anonymous.php}?
 * @todo: If not rename this to `NonRecordingSpan`.
 * @todo: Make this only implement {@see API\Span}.
 */
class NoopSpan implements ReadWriteSpan
{
    /** @var API\SpanContext */
    private $context;

    /** @var API\Attributes */
    private $attributes;

    /** @var API\Links */
    private $links;

    /** @var API\Events */
    private $events;

    /** @var API\StatusCode */
    private $status;

    /** @var bool */
    private $ended = false;
    // To allow for the span to be deactivated and not seen in getActiveSpan

    /*
     * All operations are no-op except context propagation.
     * If a valid context is passed in, the context is propagated;
     * otherwise, it returns an invalid span.
     */
    public function __construct(API\SpanContext $spanContext = null)
    {
        if (null == $spanContext) {
            $this->context = new SpanContext(
                SpanContext::INVALID_TRACE,
                SpanContext::INVALID_SPAN,
                0
            );
        } else {
            $this->context = $spanContext;
        }
        $this->attributes = new Attributes();
        $this->events = new Events();
        $this->links = new Links();
        $this->status = new StatusCode();
    }

    public function getName(): string
    {
        return '';
    }

    public function getParentContext(): ?API\SpanContext
    {
        return null;
    }

    public function getStartEpochTimestamp(): int
    {
        return 0;
    }

    public function getStart(): int
    {
        return 0;
    }

    public function getEnd(): ?int
    {
        return 0;
    }

    public function getDuration(): ?int
    {
        return null;
    }
    public function getAttributes(): API\Attributes
    {
        return $this->attributes;
    }

    public function getLinks(): API\Links
    {
        return $this->links;
    }

    public function getEvents(): API\Events
    {
        return $this->events;
    }

    public function getStatus(): API\StatusCode
    {
        return $this->status;
    }

    public function setAttribute(string $key, $value): API\Span
    {
        return $this;
    }

    public function addEvent(string $name, ?API\Attributes $attributes = null, int $timestamp = null): API\Span
    {
        return $this;
    }

    public function addLink(API\SpanContext $context, ?API\Attributes $attributes = null): API\Span
    {
        return $this;
    }

    public function recordException(Throwable $exception, ?API\Attributes $attributes = null): API\Span
    {
        return $this;
    }

    public function updateName(string $name): API\Span
    {
        return $this;
    }

    public function setStatus(string $code, ?string $description = null): API\Span
    {
        return $this;
    }

    public function end(int $timestamp = null): API\Span
    {
        $this->ended = true;

        return $this;
    }
    public function hasEnded(): bool
    {
        return $this->ended;
    }

    public function isRecording(): bool
    {
        return false;
    }

    public function isRemote(): bool
    {
        return $this->context->isRemote();
    }

    public function isSampled(): bool
    {
        return false;
    }

    public function getKind(): int
    {
        return API\SpanKind::KIND_INTERNAL;
    }

    public function getCanonicalStatusCode(): string
    {
        return $this->status->getCanonicalStatusCode();
    }

    public function getStatusDescription(): string
    {
        return $this->status->getStatusDescription();
    }

    public function isStatusOk(): bool
    {
        return $this->status->isStatusOK();
    }

    public function getContext(): API\SpanContext
    {
        return $this->context;
    }

    public function getEndEpochTimestamp(): ?int
    {
        return null;
    }

    public function getResource(): ResourceInfo
    {
        return ResourceInfo::emptyResource();
    }

    public function getInstrumentationLibrary(): InstrumentationLibrary
    {
        return new InstrumentationLibrary('');
    }

    public function activate(): Scope
    {
        return Context::getCurrent()->withContextValue($this)->activate();
    }

    public function storeInContext(Context $context): Context
    {
        return $context->with(SpanContextKey::instance(), $this);
    }
}
