<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextStorageInterface;

final class NoopSpanBuilder implements SpanBuilderInterface
{
    private ContextStorageInterface $contextStorage;

    /** @var ContextInterface|false|null */
    private $parentContext = null;

    public function __construct(ContextStorageInterface $contextStorage)
    {
        $this->contextStorage = $contextStorage;
    }

    public function setParent($context): SpanBuilderInterface
    {
        $this->parentContext = $context;

        return $this;
    }

    public function addLink(SpanContextInterface $context, iterable $attributes = []): SpanBuilderInterface
    {
        return $this;
    }

    public function setAttribute(string $key, $value): SpanBuilderInterface
    {
        return $this;
    }

    public function setAttributes(iterable $attributes): SpanBuilderInterface
    {
        return $this;
    }

    public function setStartTimestamp(int $timestamp): SpanBuilderInterface
    {
        return $this;
    }

    public function setSpanKind(int $spanKind): SpanBuilderInterface
    {
        return $this;
    }

    public function startSpan(): SpanInterface
    {
        $parentContext = Context::resolve($this->parentContext, $this->contextStorage);
        $span = Span::fromContext($parentContext);
        if ($span->isRecording()) {
            $span = Span::wrap($span->getContext());
        }

        return $span;
    }
}
