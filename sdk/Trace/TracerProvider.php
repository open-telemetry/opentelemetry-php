<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class TracerProvider implements API\TracerProvider
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var Tracer[]
     */
    protected $tracers;

    /**
     * @var SpanProcessor[]
     */
    protected $spanProcessors;

    /**
     * TracerFactory constructor.
     *
     * @param SpanProcessor[] $spanProcessors
     */
    final private function __construct(array $spanProcessors = [])
    {
        foreach ($spanProcessors as $spanProcessor) {
            if (!$spanProcessor instanceof SpanProcessor) {
                throw new \TypeError(
                    sprintf(
                        'Span Processors should be of type %s, but object of type %s provided',
                        SpanProcessor::class,
                        gettype($spanProcessor) == 'object' ? get_class($spanProcessor) : gettype($spanProcessor)
                    )
                );
            }
        }

        $this->spanProcessors = $spanProcessors;
    }

    /**
     * @param SpanProcessor[] $spanProcessors
     *
     * @return static
     */
    public static function getInstance(array $spanProcessors = []): self
    {
        if (self::$instance instanceof TracerProvider) {
            return self::$instance;
        }

        $instance = new TracerProvider($spanProcessors);

        return self::$instance = $instance;
    }

    public function getTracer(string $name, ?string $version = ''): API\Tracer
    {
        if (isset($this->tracers[$name]) && $this->tracers[$name] instanceof API\Tracer) {
            return $this->tracers[$name];
        }

        $spanContext = SpanContext::generate();

        return $this->tracers[$name] = new Tracer($this->spanProcessors, $spanContext);
    }
}
