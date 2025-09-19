<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\API\Logs\EventLoggerProviderInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\NoopEventLoggerProvider;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ImplicitContextKeyedInterface;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
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
    private ?EventLoggerProviderInterface $eventLoggerProvider = null;
    private ?ResponsePropagatorInterface $responsePropagator = null;

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
     * @phan-suppress PhanDeprecatedFunction
     */
    public static function createNoop(): Configurator
    {
        return self::create()
            ->withTracerProvider(new NoopTracerProvider())
            ->withMeterProvider(new NoopMeterProvider())
            ->withPropagator(new NoopTextMapPropagator())
            ->withLoggerProvider(NoopLoggerProvider::getInstance())
            ->withEventLoggerProvider(new NoopEventLoggerProvider())
            ->withResponsePropagator(new NoopResponsePropagator())
        ;
    }

    #[\Override]
    public function activate(): ScopeInterface
    {
        return $this->storeInContext()->activate();
    }

    /**
     * @phan-suppress PhanDeprecatedFunction
     */
    #[\Override]
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
        if ($this->eventLoggerProvider !== null) {
            $context = $context->with(ContextKeys::eventLoggerProvider(), $this->eventLoggerProvider);
        }
        if ($this->responsePropagator !== null) {
            $context = $context->with(ContextKeys::responsePropagator(), $this->responsePropagator);
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

    /**
     * @deprecated
     */
    public function withEventLoggerProvider(?EventLoggerProviderInterface $eventLoggerProvider): Configurator
    {
        $self = clone $this;
        $self->eventLoggerProvider = $eventLoggerProvider;

        return $self;
    }

    public function withResponsePropagator(?ResponsePropagatorInterface $responsePropagator): Configurator
    {
        $self = clone $this;
        $self->responsePropagator = $responsePropagator;

        return $self;
    }
}
