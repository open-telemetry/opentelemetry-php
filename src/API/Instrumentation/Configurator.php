<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ImplicitContextKeyedInterface;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Context\ScopeInterface;

/**
 * Configures the global (context scoped) instrumentation instances.
 *
 * @see Configurator::activate()
 */
final class Configurator implements ImplicitContextKeyedInterface
{
    private ?TracerProviderInterface $tracerProvider = null;
    private ?MeterProviderInterface $meterProvider = null;
    private ?TextMapPropagatorInterface $propagator = null;
    private ?LoggerProviderInterface $loggerProvider = null;

    private function __construct()
    {
    }

    /**
     * Creates a configurator that uses parent instances for not configured values.
     */
    public static function create(): Configurator
    {
        return new self();
    }

    /**
     * Creates a configurator that uses noop instances for not configured values.
     */
    public static function createNoop(): Configurator
    {
        return self::create()
            ->withTracerProvider(new NoopTracerProvider())
            ->withMeterProvider(new NoopMeterProvider())
            ->withPropagator(new NoopTextMapPropagator())
            ->withLoggerProvider(new NoopLoggerProvider())
        ;
    }

    public function activate(): ScopeInterface
    {
        return $this->storeInContext()->activate();
    }

    public function storeInContext(?ContextInterface $context = null): ContextInterface
    {
        $context ??= Context::getCurrent();

        if ($this->tracerProvider !== null) {
            $context = $context->with(ContextKeys::tracerProvider(), $this->tracerProvider);
        }
        if ($this->meterProvider !== null) {
            $context = $context->with(ContextKeys::meterProvider(), $this->meterProvider);
        }
        if ($this->propagator !== null) {
            $context = $context->with(ContextKeys::propagator(), $this->propagator);
        }
        if ($this->loggerProvider !== null) {
            $context = $context->with(ContextKeys::loggerProvider(), $this->loggerProvider);
        }

        return $context;
    }

    public function withTracerProvider(?TracerProviderInterface $tracerProvider): Configurator
    {
        $self = clone $this;
        $self->tracerProvider = $tracerProvider;

        return $self;
    }

    public function withMeterProvider(?MeterProviderInterface $meterProvider): Configurator
    {
        $self = clone $this;
        $self->meterProvider = $meterProvider;

        return $self;
    }

    public function withPropagator(?TextMapPropagatorInterface $propagator): Configurator
    {
        $self = clone $this;
        $self->propagator = $propagator;

        return $self;
    }

    public function withLoggerProvider(?LoggerProviderInterface $loggerProvider): Configurator
    {
        $self = clone $this;
        $self->loggerProvider = $loggerProvider;

        return $self;
    }
}
