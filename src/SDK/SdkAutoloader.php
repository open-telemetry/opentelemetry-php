<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariables;
use OpenTelemetry\SDK\Common\Environment\Variables;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Propagation\PropagatorFactory;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;

class SdkAutoloader
{
    private static ?ScopeInterface $scope = null;
    private static ?bool $enabled = null;

    public static function autoload(?SdkBuilder $builder = null): bool
    {
        if (self::$enabled === null) {
            self::$enabled = EnvironmentVariables::getBoolean(Variables::OTEL_PHP_AUTOLOAD_ENABLED);
        }
        if (!self::$enabled || self::$scope) {
            return false;
        }
        $exporter = (new ExporterFactory())->fromEnvironment();
        $propagator = (new PropagatorFactory())->create();
        $meterProvider = (new MeterProviderFactory())->create();
        $spanProcessor = (new SpanProcessorFactory())->fromEnvironment($exporter, $meterProvider);
        $tracerProvider = (new TracerProviderBuilder())
            ->addSpanProcessor($spanProcessor)
            ->setSampler((new SamplerFactory())->fromEnvironment())
            ->build();
        $builder ??= Sdk::builder();
        self::$scope = $builder
            ->setTracerProvider($tracerProvider)
            ->setPropagator($propagator)
            ->setMeterProvider($meterProvider)
            ->setAutoShutdown(true)
            ->buildAndRegisterGlobal();
        ShutdownHandler::register(function () {
            if (self::$scope !== null) {
                self::$scope->detach();
            }
        });

        return true;
    }

    /**
     * @internal
     */
    public static function shutdown(): void
    {
        if (self::$scope !== null) {
            self::$scope->detach();
            self::$scope = null;
        }
        self::$enabled = null;
    }
}
