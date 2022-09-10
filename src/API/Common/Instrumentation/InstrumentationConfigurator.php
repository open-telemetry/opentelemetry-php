<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Instrumentation;

use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Context\ScopeInterface;
use Psr\Log\LoggerInterface;

final class InstrumentationConfigurator
{
    private ?ContextStorageInterface $contextStorage;

    private ?TracerProviderInterface $tracerProvider = null;
    private ?MeterProviderInterface $meterProvider = null;
    private ?LoggerInterface $logger = null;
    private ?TextMapPropagatorInterface $propagator = null;

    private function __construct(?ContextStorageInterface $contextStorage)
    {
        $this->contextStorage = $contextStorage;
    }

    public static function create(?ContextStorageInterface $contextStorage = null): InstrumentationConfigurator
    {
        return new self($contextStorage);
    }

    public function activate(): ScopeInterface
    {
        $contextStorage = $this->contextStorage ?? Context::storage();
        $context = $contextStorage->current();

        if ($this->tracerProvider !== null) {
            $context = $context->with(ContextKeys::tracerProvider(), $this->tracerProvider);
        }
        if ($this->meterProvider !== null) {
            $context = $context->with(ContextKeys::meterProvider(), $this->meterProvider);
        }
        if ($this->logger !== null) {
            $context = $context->with(ContextKeys::logger(), $this->logger);
        }
        if ($this->propagator !== null) {
            $context = $context->with(ContextKeys::propagator(), $this->propagator);
        }

        return $contextStorage->attach($context);
    }

    public function withTracerProvider(?TracerProviderInterface $tracerProvider): InstrumentationConfigurator
    {
        $self = clone $this;
        $self->tracerProvider = $tracerProvider;

        return $self;
    }

    public function withMeterProvider(?MeterProviderInterface $meterProvider): InstrumentationConfigurator
    {
        $self = clone $this;
        $self->meterProvider = $meterProvider;

        return $self;
    }

    public function withLogger(?LoggerInterface $logger): InstrumentationConfigurator
    {
        $self = clone $this;
        $self->logger = $logger;

        return $self;
    }

    public function withPropagator(?TextMapPropagatorInterface $propagator): InstrumentationConfigurator
    {
        $self = clone $this;
        $self->propagator = $propagator;

        return $self;
    }
}
