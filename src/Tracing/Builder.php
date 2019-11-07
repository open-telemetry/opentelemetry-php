<?php

declare(strict_types=1);

namespace OpenTelemetry\Tracing;

// todo: This is not in the spec, and it doesn't seem to add any value over using the span/tracer API
class Builder
{
    private $spanContext;

    public static function create()
    {
        return new self;
    }

    public function setSpanContext(SpanContext $spanContext) : self
    {
        $this->spanContext = $spanContext;
        return $this;
    }

    public function getTracer()
    {
        return new Tracer($this->spanContext);
    }
}