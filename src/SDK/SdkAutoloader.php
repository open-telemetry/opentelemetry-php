<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\API\Common\Instrumentation\Configurator;
use OpenTelemetry\API\Common\Instrumentation\Globals;
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
    private static ?bool $enabled = null;

    public static function autoload(): bool
    {
        self::$enabled ??= EnvironmentVariables::getBoolean(Variables::OTEL_PHP_AUTOLOAD_ENABLED);
        if (!self::$enabled) {
            return false;
        }
        Globals::registerInitializer(function (Configurator $configurator) {
            $exporter = (new ExporterFactory())->fromEnvironment();
            $propagator = (new PropagatorFactory())->create();
            $meterProvider = (new MeterProviderFactory())->create();
            $spanProcessor = (new SpanProcessorFactory())->fromEnvironment($exporter, $meterProvider);
            $tracerProvider = (new TracerProviderBuilder())
                ->addSpanProcessor($spanProcessor)
                ->setSampler((new SamplerFactory())->fromEnvironment())
                ->build();

            ShutdownHandler::register([$tracerProvider, 'shutdown']);
            ShutdownHandler::register([$meterProvider, 'shutdown']);

            return $configurator
                ->withTracerProvider($tracerProvider)
                ->withMeterProvider($meterProvider)
                ->withPropagator($propagator);
        });

        return true;
    }

    /**
     * @internal
     */
    public static function shutdown(): void
    {
        self::$enabled = null;
    }
}
