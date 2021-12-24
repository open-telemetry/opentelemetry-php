<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace\AttributesInterface;
use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;

final class NoopSpanBuilder implements SpanBuilderInterface
{
    private ?Context $parent = null;

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

    public function addLink(SpanContextInterface $context, AttributesInterface $attributes = null): SpanBuilderInterface
    {
        return $this;
    }

    public function setAttribute(string $key, $value): SpanBuilderInterface
    {
        return $this;
    }

    public function setAttributes(AttributesInterface $attributes): SpanBuilderInterface
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
        $span = Span::fromContext($this->parent ?? Context::getCurrent());
        if ($span->isRecording()) {
            $span = Span::wrap($span->getContext());
        }

        return $span;
    }
}
