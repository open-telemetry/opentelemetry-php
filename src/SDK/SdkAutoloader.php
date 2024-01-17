<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use InvalidArgumentException;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Logs\LoggerProviderFactory;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Propagation\PropagatorFactory;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;

class SdkAutoloader
{
    private static ?bool $enabled = null;

    public static function autoload(): bool
    {
        try {
            self::$enabled ??= Configuration::getBoolean(Variables::OTEL_PHP_AUTOLOAD_ENABLED);
        } catch (InvalidArgumentException $e) {
            //invalid setting, assume false
            self::$enabled = false;
        }
        if (!self::$enabled) {
            return false;
        }
        Globals::registerInitializer(function (Configurator $configurator) {
            $propagator = (new PropagatorFactory())->create();
            if (Sdk::isDisabled()) {
                //@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#general-sdk-configuration
                return $configurator->withPropagator($propagator);
            }
            $emitMetrics = Configuration::getBoolean(Variables::OTEL_PHP_INTERNAL_METRICS_ENABLED);

            $resource = ResourceInfoFactory::defaultResource();
            $exporter = (new ExporterFactory())->create();
            $meterProvider = (new MeterProviderFactory())->create($resource);
            $spanProcessor = (new SpanProcessorFactory())->create($exporter, $emitMetrics ? $meterProvider : null);
            $tracerProvider = (new TracerProviderBuilder())
                ->addSpanProcessor($spanProcessor)
                ->setResource($resource)
                ->setSampler((new SamplerFactory())->create())
                ->build();

            $loggerProvider = (new LoggerProviderFactory())->create($emitMetrics ? $meterProvider : null, $resource);

            ShutdownHandler::register([$tracerProvider, 'shutdown']);
            ShutdownHandler::register([$meterProvider, 'shutdown']);
            ShutdownHandler::register([$loggerProvider, 'shutdown']);

            return $configurator
                ->withTracerProvider($tracerProvider)
                ->withMeterProvider($meterProvider)
                ->withLoggerProvider($loggerProvider)
                ->withPropagator($propagator)
                ;
        });

        return true;
    }

    /**
     * @internal
     */
    public static function reset(): void
    {
        self::$enabled = null;
    }
}
