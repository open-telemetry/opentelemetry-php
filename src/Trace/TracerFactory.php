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

    private final function __construct()
    {
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

        foreach ($spanProcessors as $spanProcessor) {
            if (!$spanProcessor instanceof SpanProcessorInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Span Processors should be of type %s, but object of type %s provided",
                        SpanProcessorInterface::class,
                        get_class($spanProcessor)
                    )
                );
            }
        }

        $instance = new TracerFactory();
        $instance->spanProcessors = $spanProcessors;

        return self::$instance = $instance;
    }

    public function getTracer(string $name, string $version = ""): Tracer {

        if ($this->tracers[$name] instanceof Tracer) {
            return $this->tracers[$name];
        }

        $spanContext = SpanContext::generate();
        return $this->tracers[$name] = new Tracer($spanContext);
    }
}