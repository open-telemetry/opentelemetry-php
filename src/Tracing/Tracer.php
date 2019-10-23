<?php

declare(strict_types=1);

namespace OpenTelemetry\Tracing;

use OpenTelemetry\Tracing\Sampler\AlwaysSampleSampler;
use OpenTelemetry\Tracing\Sampler\SamplerInterface;
class Tracer
{
    private $active;
    private $spans = [];
    private $tail = [];

    /**
     * @param SpanContext $context
     * @param array $options 
     */
    public function __construct(SpanContext $context = null, array $options = [])
    {
        $sampler = array_key_exists('sampler', $options)
            ? $options['sampler']
            : new AlwaysSampleSampler();
        unset($options['sampler']);

        print_r($sampler);
        $context = $context ?: SpanContext::generate();
        $this->active = $this->generateSpanInstance('tracer', $context);
    }

    public function getActiveSpan() : Span
    {
        while (count($this->tail) && $this->active->getEnd()) {
            $this->active = array_pop($this->tail);
        }
        return $this->active;
    }

    public function setActive(Span $span) : Span
    {
        $this->tail[] = $this->active;
        return $this->active = $span;
    }

    public function createSpan(string $name) : Span
    {
        $parent = $this->getActiveSpan()->getSpanContext();
        $context = SpanContext::fork($parent->getTraceId());
        $span = $this->generateSpanInstance($name, $context);
        return $this->setActive($span);
    }

    public function getSpans() : array
    {
        return $this->spans;
    }

    private function generateSpanInstance($name, SpanContext $context) : Span
    {
        $parent = null;
        if ($this->active) {
            $parent = $this->getActiveSpan()->getSpanContext();
        }
        $span = new Span($name, $context, $parent);
        $this->spans[] = $span;
        return $span;
    }
}