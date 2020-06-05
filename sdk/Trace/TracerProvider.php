<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

final class TracerProvider implements API\TracerProvider
{

    /**
     * @var Tracer[]
     */
    protected $tracers;

    /**
     * @var SpanMultiProcessor
     */
    protected $spanProcessors;

    public function __construct()
    {
        $this->spanProcessors = new SpanMultiProcessor();
    }

    public function getTracer(string $name, ?string $version = ''): API\Tracer
    {
        if (isset($this->tracers[$name]) && $this->tracers[$name] instanceof API\Tracer) {
            return $this->tracers[$name];
        }

        $spanContext = SpanContext::generate();

        return $this->tracers[$name] = new Tracer($this, $spanContext);
    }

    public function addSpanProcessor(SpanProcessor $processor): self
    {
        $this->spanProcessors->addSpanProcessor($processor);

        return $this;
    }

    public function getSpanProcessor(): SpanMultiProcessor
    {
        return $this->spanProcessors;
    }
}
