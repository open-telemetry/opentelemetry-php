<?php

namespace OpenTelemetry\Trace;

use InvalidArgumentException;
use OpenTelemetry\Context\SpanContext;
use OpenTelemetry\Trace\SpanProcessorInterface;

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
     * @var SpanProcessorInterface[]
     */
    protected $spanProcessors;

    /**
     * TracerFactory constructor.
     *
     * @param SpanProcessorInterface[] $spanProcessors
     */
    final private function __construct(array $spanProcessors = [])
    {
        foreach ($spanProcessors as $spanProcessor) {
            if (!$spanProcessor instanceof SpanProcessorInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Span Processors should be of type %s, but object of type %s provided",
                        SpanProcessorInterface::class,
                        gettype($spanProcessor) == "object" ? get_class($spanProcessor) : gettype($spanProcessor)
                    )
                );
            }
        }

        $this->spanProcessors = $spanProcessors;
    }

    /**
     * @param SpanProcessorInterface[] $spanProcessors
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

    public function getTracer(string $name, string $version = ""): Tracer
    {

        if ($this->tracers[$name] instanceof Tracer) {
            return $this->tracers[$name];
        }

        $spanContext = SpanContext::generate();
        return $this->tracers[$name] = new Tracer($spanContext);
    }
}