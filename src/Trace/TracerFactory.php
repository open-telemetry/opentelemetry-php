<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

use InvalidArgumentException;
use OpenTelemetry\Context\SpanContext;
use OpenTelemetry\Trace\SpanProcessor\SpanProcessor;

class TracerFactory
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
                throw new InvalidArgumentException(
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
        if (self::$instance instanceof TracerFactory) {
            return self::$instance;
        }

        $instance = new TracerFactory($spanProcessors);

        return self::$instance = $instance;
    }

    public function getTracer(string $name, string $version = ''): Tracer
    {
        if (isset($this->tracers[$name]) && $this->tracers[$name] instanceof Tracer) {
            return $this->tracers[$name];
        }

        $spanContext = SpanContext::generate();

        return $this->tracers[$name] = new Tracer($this->spanProcessors, $spanContext);
    }
}
