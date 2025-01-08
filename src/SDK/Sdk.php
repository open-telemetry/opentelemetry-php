<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\API\Logs\EventLoggerProviderInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;

class Sdk
{
    private const OTEL_PHP_DISABLED_INSTRUMENTATIONS_ALL = 'all';

    public function __construct(
        private readonly TracerProviderInterface $tracerProvider,
        private readonly MeterProviderInterface $meterProvider,
        private readonly LoggerProviderInterface $loggerProvider,
        private readonly EventLoggerProviderInterface $eventLoggerProvider,
        private readonly TextMapPropagatorInterface $propagator,
    ) {
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
        $disabledInstrumentations = Configuration::getList(Variables::OTEL_PHP_DISABLED_INSTRUMENTATIONS);

        return [self::OTEL_PHP_DISABLED_INSTRUMENTATIONS_ALL] === $disabledInstrumentations || in_array($name, $disabledInstrumentations);
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

    /**
     * @deprecated
     */
    public function getEventLoggerProvider(): EventLoggerProviderInterface
    {
        return $this->eventLoggerProvider;
    }

    public function getPropagator(): TextMapPropagatorInterface
    {
        return $this->propagator;
    }
}
