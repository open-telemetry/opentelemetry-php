<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;

class Sdk
{
    private TracerProviderInterface $tracerProvider;
    private MeterProviderInterface $meterProvider;
    private LoggerProviderInterface $loggerProvider;
    private TextMapPropagatorInterface $propagator;

    public function __construct(
        TracerProviderInterface $tracerProvider,
        MeterProviderInterface $meterProvider,
        LoggerProviderInterface $loggerProvider,
        TextMapPropagatorInterface $propagator
    ) {
        $this->tracerProvider = $tracerProvider;
        $this->meterProvider = $meterProvider;
        $this->loggerProvider = $loggerProvider;
        $this->propagator = $propagator;
    }

    public static function isDisabled(): bool
    {
        return Configuration::getBoolean(Variables::OTEL_SDK_DISABLED);
    }

    /**
     * Tests whether an auto-instrumentation package has been disabled by config
     */
    public static function isInstrumentationDisabled(string $name): bool
    {
        return in_array($name, Configuration::getList(Variables::OTEL_PHP_DISABLED_INSTRUMENTATIONS));
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

    public function getLoggerProvider(): LoggerProviderInterface
    {
        return $this->loggerProvider;
    }

    public function getPropagator(): TextMapPropagatorInterface
    {
        return $this->propagator;
    }
}
