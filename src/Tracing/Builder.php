<?php

declare(strict_types=1);

namespace OpenTelemetry\Tracing;

class Builder
{
    private $spanContext;

    public static function create()
    {
        return new self;
    }

    public function setSpanContext(SpanContext $spanContext, options $options) : self
    {
        $this->spanContext = $spanContext;
        $this->options = $options;
        return $this;
    }

    public function getTracer()
    {
        return new Tracer($this->spanContext);
    }
}