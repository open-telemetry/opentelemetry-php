<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;

class Sdk
{
    private TracerProviderInterface $tracerProvider;
    private MeterProviderInterface $meterProvider;
    private TextMapPropagatorInterface $propagator;

    public function __construct(
        TracerProviderInterface $tracerProvider,
        MeterProviderInterface $meterProvider,
        TextMapPropagatorInterface $propagator
    ) {
        $this->tracerProvider = $tracerProvider;
        $this->meterProvider = $meterProvider;
        $this->propagator = $propagator;
    }

    public static function isDisabled(): bool
    {
        return Configuration::getBoolean(Variables::OTEL_SDK_DISABLED);
    }

    public static function builder(): SdkBuilder
    {
        return new SdkBuilder();
    }

    public function getTracerProvider(): TracerProviderInterface
    {
        return $this->tracerProvider;
    }

    public function getMeterProvider(): MeterProviderInterface
    {
        return $this->meterProvider;
    }

    public function getPropagator(): TextMapPropagatorInterface
    {
        return $this->propagator;
    }
}
