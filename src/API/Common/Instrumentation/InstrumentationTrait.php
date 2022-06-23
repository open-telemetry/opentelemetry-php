<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Instrumentation;

use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\NullPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait InstrumentationTrait
{
    private TextMapPropagatorInterface $propagator;
    private TracerProviderInterface $tracerProvider;
    private MeterProviderInterface $meterProvider;
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->propagator = new NullPropagator();
        $this->tracerProvider = new NoopTracerProvider();
        $this->meterProvider = new NoopMeterProvider();
        $this->logger = new NullLogger();
    }

    abstract public function getName(): string;

    abstract public function getVersion(): ?string;

    abstract public function getSchema(): ?string;

    public function register(): bool
    {
        // not implemented yet
        return true;
    }

    public function activate(): bool
    {
        // not implemented yet
        return true;
    }

    public function setPropagator(TextMapPropagatorInterface $propagator): void
    {
        $this->propagator = $propagator;
    }

    public function getPropagator(): TextMapPropagatorInterface
    {
        return $this->propagator;
    }

    public function setTracerProvider(TracerProviderInterface $tracerProvider): void
    {
        $this->tracerProvider = $tracerProvider;
    }

    public function getTracerProvider(): TracerProviderInterface
    {
        return $this->tracerProvider;
    }

    public function setMeterProvider(MeterProviderInterface $meterProvider): void
    {
        $this->meterProvider = $meterProvider;
    }

    public function getMeterProvider(): MeterProviderInterface
    {
        return $this->meterProvider;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
