<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\API\Common\Instrumentation\Configurator;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use OpenTelemetry\SDK\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

class SdkBuilder
{
    private ?TracerProviderInterface $tracerProvider = null;
    private ?MeterProviderInterface  $meterProvider = null;
    private ?TextMapPropagatorInterface $propagator = null;
    private bool $autoShutdown = false;

    /**
     * Automatically shut down providers on process completion. If not set, the user is responsible for calling `shutdown`.
     */
    public function setAutoShutdown(bool $shutdown): self
    {
        $this->autoShutdown = $shutdown;

        return $this;
    }

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
        $tracerProvider = $this->tracerProvider ?? new NoopTracerProvider();
        $meterProvider = $this->meterProvider ?? new NoopMeterProvider();
        if ($this->autoShutdown) {
            // rector rule disabled in config, because ShutdownHandler::register() does not keep a strong reference to $this
            ShutdownHandler::register([$tracerProvider, 'shutdown']);
            ShutdownHandler::register([$meterProvider, 'shutdown']);
        }

        return new Sdk(
            $tracerProvider,
            $meterProvider,
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

        // @todo could auto-shutdown self?
        return Context::storage()->attach($context);
    }
}
