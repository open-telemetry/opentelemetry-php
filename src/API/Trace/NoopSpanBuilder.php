<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;

final class NoopSpanBuilder implements SpanBuilderInterface
{
    private ContextStorageInterface $contextStorage;

    private ?Context $parent = null;

    public function __construct(ContextStorageInterface $contextStorage)
    {
        $this->contextStorage = $contextStorage;
    }

    public function setParent(Context $parentContext): SpanBuilderInterface
    {
        $this->parent = $parentContext;

        return $this;
    }

    public function setNoParent(): SpanBuilderInterface
    {
        $this->parent = Context::getRoot();

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
        $span = AbstractSpan::fromContext($this->parent ?? $this->contextStorage->current());
        if ($span->isRecording()) {
            $span = AbstractSpan::wrap($span->getContext());
        }

        return $span;
    }
}
