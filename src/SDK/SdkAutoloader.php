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
    public static function autoload(): bool
    {
        if (!self::isEnabled() || self::isExcludedUrl()) {
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
     * Test whether a request URI is set, and if it matches the excluded urls configuration option
     *
     * @internal
     */
    public static function isIgnoredUrl(): bool
    {
        $ignoreUrls = Configuration::getList(Variables::OTEL_PHP_EXCLUDED_URLS, []);
        if ($ignoreUrls === []) {
            return false;
        }
        $url = $_SERVER['REQUEST_URI'] ?? null;
        if (!$url) {
            return false;
        }
        foreach ($ignoreUrls as $ignore) {
            if (preg_match(sprintf('|%s|', $ignore), $url) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @internal
     */
    public static function isEnabled(): bool
    {
        try {
            $enabled = Configuration::getBoolean(Variables::OTEL_PHP_AUTOLOAD_ENABLED);
        } catch (InvalidArgumentException $e) {
            //invalid setting, assume false
            return false;
        }

        return $enabled;
    }

    /**
     * Test whether a request URI is set, and if it matches the excluded urls configuration option
     *
     * @internal
     */
    public static function isExcludedUrl(): bool
    {
        $excludedUrls = Configuration::getList(Variables::OTEL_PHP_EXCLUDED_URLS, []);
        if ($excludedUrls === []) {
            return false;
        }
        $url = $_SERVER['REQUEST_URI'] ?? null;
        if (!$url) {
            return false;
        }
        foreach ($excludedUrls as $excludedUrl) {
            if (preg_match(sprintf('|%s|', $excludedUrl), $url) === 1) {
                return true;
            }
        }

        return false;
    }
}
