<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\API\Common\Instrumentation\Configurator;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Context\ScopeInterface;

class SdkBuilder
{
    private ?TracerProviderInterface $tracerProvider = null;
    private ?MeterProviderInterface  $meterProvider = null;
    private ?TextMapPropagatorInterface $propagator = null;

    public function setTracerProvider(TracerProviderInterface $provider): self
    {
        $this->tracerProvider = $provider;

        return $this;
    }

    public function setMeterProvider(MeterProviderInterface $meterProvider): self
    {
        $this->meterProvider = $meterProvider;

        return $this;
    }

    public function setPropagator(TextMapPropagatorInterface $propagator): self
    {
        $this->propagator = $propagator;

        return $this;
    }

    public function build(): Sdk
    {
        return new Sdk(
            $this->tracerProvider ?? new NoopTracerProvider(),
            $this->meterProvider ?? new NoopMeterProvider(),
            $this->propagator ?? NoopTextMapPropagator::getInstance(),
        );
    }

    public function buildAndRegisterGlobal(): ScopeInterface
    {
        $sdk = $this->build();
        $context = Configurator::create()
            ->withPropagator($sdk->getPropagator())
            ->withTracerProvider($sdk->getTracerProvider())
            ->withMeterProvider($sdk->getMeterProvider())
            ->storeInContext();

        return Context::storage()->attach($context);
    }
}
