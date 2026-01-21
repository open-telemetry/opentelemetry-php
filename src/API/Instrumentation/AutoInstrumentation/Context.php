<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * Context used for component creation.
 */
final readonly class Context
{
    public function __construct(
        public TracerProviderInterface $tracerProvider = new NoopTracerProvider(),
        public MeterProviderInterface $meterProvider = new NoopMeterProvider(),
        public LoggerProviderInterface $loggerProvider = new NoopLoggerProvider(),
        public TextMapPropagatorInterface $propagator = new NoopTextMapPropagator(),
        public ResponsePropagatorInterface $responsePropagator = new NoopResponsePropagator(),
    ) {
    }
}
