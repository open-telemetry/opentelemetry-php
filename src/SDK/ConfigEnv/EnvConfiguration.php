<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv;

use function array_key_first;
use function array_values;
use function count;
use LogicException;
use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Context\Propagation\MultiResponsePropagator;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactory;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\EnvComponentLoaderRegistry;
use OpenTelemetry\SDK\Common\Configuration\EnvResolver;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Util\LogsMessagesLogger;
use OpenTelemetry\SDK\ConfigEnv\Compatibility\LogRecordExporterLoaderLogRecordExporterFactory;
use OpenTelemetry\SDK\ConfigEnv\Compatibility\MetricExporterLoaderMetricExporterFactory;
use OpenTelemetry\SDK\ConfigEnv\Compatibility\ResponsePropagatorLoaderResponsePropagator;
use OpenTelemetry\SDK\ConfigEnv\Compatibility\SpanExporterLoaderSpanExporterFactory;
use OpenTelemetry\SDK\ConfigEnv\Compatibility\TextMapPropagatorLoaderTextMapPropagator;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderBuilder;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordLimits;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderBuilder;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanLimits;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;

final class EnvConfiguration
{
    public static function loadFromEnv(): SdkBuilder
    {
        $env = new EnvResolver();
        $context = new Context(
            logger: new LogsMessagesLogger(),
        );

        $registry = self::loaderRegistry();

        $propagator = self::propagator($registry, $env, $context);
        $responsePropagator = self::responsePropagator($registry, $env, $context);

        $sdkBuilder = new SdkBuilder();
        $sdkBuilder->setPropagator($propagator);
        $sdkBuilder->setResponsePropagator($responsePropagator);

        if ($env->bool(Variables::OTEL_SDK_DISABLED)) {
            //@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#general-sdk-configuration
            return $sdkBuilder;
        }

        $tracerProviderBuilder = TracerProvider::builder();
        $meterProviderBuilder = MeterProvider::builder();
        $loggerProviderBuilder = LoggerProvider::builder();

        $resource = ResourceInfoFactory::defaultResource();
        $tracerProviderBuilder->setResource($resource);
        $meterProviderBuilder->setResource($resource);
        $loggerProviderBuilder->setResource($resource);

        self::meterProvider($meterProviderBuilder, $registry, $env, $context);

        $meterProvider = $meterProviderBuilder->build();

        if ($env->bool(Variables::OTEL_PHP_INTERNAL_METRICS_ENABLED)) {
            // TODO should instead provide all {Signal}Providers to SDK component loaders for self-diagnostics
            $context = new Context(
                meterProvider: $meterProvider,
                logger: $context->logger,
            );
        }

        self::tracerProvider($tracerProviderBuilder, $registry, $env, $context);
        self::loggerProvider($loggerProviderBuilder, $registry, $env, $context);

        $tracerProvider = $tracerProviderBuilder->build();
        $loggerProvider = $loggerProviderBuilder->build();

        $sdkBuilder->setTracerProvider($tracerProvider);
        $sdkBuilder->setMeterProvider($meterProvider);
        $sdkBuilder->setLoggerProvider($loggerProvider);

        return $sdkBuilder;
    }

    /**
     * @phan-suppress PhanTypeMismatchArgument
     */
    private static function propagator(EnvComponentLoaderRegistry $registry, EnvResolver $env, Context $context): TextMapPropagatorInterface
    {
        $propagators = [];
        foreach ($env->list(Variables::OTEL_PROPAGATORS) ?? ['tracecontext', 'baggage'] as $propagator) {
            $propagators[$propagator] = $registry->load(TextMapPropagatorInterface::class, $propagator, $env, $context);
        }

        return match (count($propagators)) {
            0 => new NoopTextMapPropagator(),
            1 => $propagators[array_key_first($propagators)],
            default => new MultiTextMapPropagator(array_values($propagators)),
        };
    }

    /**
     * @phan-suppress PhanTypeMismatchArgument
     */
    private static function responsePropagator(EnvComponentLoaderRegistry $registry, EnvResolver $env, Context $context): ResponsePropagatorInterface
    {
        $propagators = [];
        foreach ($env->list(Variables::OTEL_EXPERIMENTAL_RESPONSE_PROPAGATORS) ?? [] as $propagator) {
            $propagators[$propagator] = $registry->load(ResponsePropagatorInterface::class, $propagator, $env, $context);
        }

        return match (count($propagators)) {
            0 => new NoopResponsePropagator(),
            1 => $propagators[array_key_first($propagators)],
            default => new MultiResponsePropagator(array_values($propagators)),
        };
    }

    /**
     * @phan-suppress PhanTypeMismatchArgument
     */
    private static function tracerProvider(TracerProviderBuilder $tracerProviderBuilder, EnvComponentLoaderRegistry $registry, EnvResolver $env, Context $context): void
    {
        $spanAttributeCountLimit = $env->int(Variables::OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT) ?? $env->int(Variables::OTEL_ATTRIBUTE_COUNT_LIMIT) ?? Defaults::OTEL_ATTRIBUTE_COUNT_LIMIT;
        $spanAttributeValueLengthLimit = $env->int(Variables::OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT) ?? $env->int(Variables::OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT) ?? Defaults::OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT;

        $tracerProviderBuilder->setSpanLimits(new SpanLimits(
            attributesFactory: new AttributesFactory(
                attributeCountLimit: $spanAttributeCountLimit,
                attributeValueLengthLimit: $spanAttributeValueLengthLimit,
            ),
            eventAttributesFactory: new AttributesFactory(
                attributeCountLimit: $env->int(Variables::OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT) ?? $spanAttributeCountLimit,
                attributeValueLengthLimit: $spanAttributeValueLengthLimit,
            ),
            linkAttributesFactory: new AttributesFactory(
                attributeCountLimit: $env->int(Variables::OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT) ?? $spanAttributeCountLimit,
                attributeValueLengthLimit: $spanAttributeValueLengthLimit,
            ),
            eventCountLimit: $env->int(Variables::OTEL_SPAN_EVENT_COUNT_LIMIT) ?? Defaults::OTEL_SPAN_EVENT_COUNT_LIMIT,
            linkCountLimit: $env->int(Variables::OTEL_SPAN_LINK_COUNT_LIMIT) ?? Defaults::OTEL_SPAN_LINK_COUNT_LIMIT,
        ));

        $tracerProviderBuilder->setSampler($registry->load(SamplerInterface::class, $env->string(Variables::OTEL_TRACES_SAMPLER) ?? Defaults::OTEL_TRACES_SAMPLER, $env, $context));
        foreach ($env->list(Variables::OTEL_TRACES_EXPORTER) ?? [Defaults::OTEL_TRACES_EXPORTER] as $spanExporter) {
            if ($spanExporter === 'none') {
                continue;
            }

            // TODO Deprecate env variable and pair automatically ?
            $spanProcessor = $env->enum(Variables::OTEL_PHP_TRACES_PROCESSOR, ['batch', 'simple', 'none']) ?? 'batch';
            if ($spanProcessor === 'none') {
                continue;
            }

            $tracerProviderBuilder->addSpanProcessor(match ($spanProcessor) {
                'batch' => new BatchSpanProcessor(
                    exporter: $registry->load(SpanExporterInterface::class, $spanExporter, $env, $context),
                    clock: Clock::getDefault(),
                    maxQueueSize: $env->int(Variables::OTEL_BSP_MAX_QUEUE_SIZE) ?? Defaults::OTEL_BSP_MAX_QUEUE_SIZE,
                    scheduledDelayMillis: $env->int(Variables::OTEL_BSP_SCHEDULE_DELAY) ?? Defaults::OTEL_BSP_SCHEDULE_DELAY,
                    exportTimeoutMillis: $env->int(Variables::OTEL_BSP_EXPORT_TIMEOUT) ?? Defaults::OTEL_BSP_EXPORT_TIMEOUT,
                    maxExportBatchSize: $env->int(Variables::OTEL_BSP_MAX_EXPORT_BATCH_SIZE) ?? Defaults::OTEL_BSP_MAX_EXPORT_BATCH_SIZE,
                    autoFlush: true,
                    meterProvider: $context->meterProvider,
                ),
                'simple' => new SimpleSpanProcessor(
                    exporter: $registry->load(SpanExporterInterface::class, $spanExporter, $env, $context),
                ),
            });
        }
    }

    /**
     * @phan-suppress PhanTypeMismatchArgument
     */
    private static function meterProvider(MeterProviderBuilder $meterProviderBuilder, EnvComponentLoaderRegistry $registry, EnvResolver $env, Context $context): void
    {
        $meterProviderBuilder->setExemplarFilter(match ($env->enum(Variables::OTEL_METRICS_EXEMPLAR_FILTER, ['always_on', 'always_off', 'trace_based']) ?? 'trace_based') {
            'always_on' => new AllExemplarFilter(),
            'always_off' => new NoneExemplarFilter(),
            'trace_based' => new WithSampledTraceExemplarFilter(),
        });
        foreach ($env->list(Variables::OTEL_METRICS_EXPORTER) ?? [Defaults::OTEL_METRICS_EXPORTER] as $metricExporter) {
            if ($metricExporter === 'none') {
                continue;
            }

            $meterProviderBuilder->addReader(new ExportingReader(
                exporter: $registry->load(MetricExporterInterface::class, $metricExporter, $env, $context),
            ));
        }
    }

    /**
     * @phan-suppress PhanTypeMismatchArgument
     */
    private static function loggerProvider(LoggerProviderBuilder $loggerProviderBuilder, EnvComponentLoaderRegistry $registry, EnvResolver $env, Context $context): void
    {
        $logRecordAttributeCountLimit = $env->int(Variables::OTEL_LOGRECORD_ATTRIBUTE_COUNT_LIMIT) ?? $env->int(Variables::OTEL_ATTRIBUTE_COUNT_LIMIT) ?? Defaults::OTEL_ATTRIBUTE_COUNT_LIMIT;
        $logRecordAttributeValueLengthLimit = $env->int(Variables::OTEL_LOGRECORD_ATTRIBUTE_VALUE_LENGTH_LIMIT) ?? $env->int(Variables::OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT) ?? Defaults::OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT;

        $loggerProviderBuilder->setLogRecordLimits(new LogRecordLimits(
            attributesFactory: new AttributesFactory(
                attributeCountLimit: $logRecordAttributeCountLimit,
                attributeValueLengthLimit: $logRecordAttributeValueLengthLimit,
            ),
        ));

        foreach ($env->list(Variables::OTEL_LOGS_EXPORTER) ?? [Defaults::OTEL_LOGS_EXPORTER] as $logRecordExporter) {
            if ($logRecordExporter === 'none') {
                continue;
            }

            // TODO Deprecate env variable and pair automatically ?
            $logRecordProcessor = $env->enum(Variables::OTEL_PHP_LOGS_PROCESSOR, ['batch', 'simple', 'none']) ?? 'batch';
            if ($logRecordProcessor === 'none') {
                continue;
            }

            $loggerProviderBuilder->addLogRecordProcessor(match ($logRecordProcessor) {
                'batch' => new BatchLogRecordProcessor(
                    exporter: $registry->load(LogRecordExporterInterface::class, $logRecordExporter, $env, $context),
                    clock: Clock::getDefault(),
                    maxQueueSize: $env->int(Variables::OTEL_BLRP_MAX_QUEUE_SIZE) ?? Defaults::OTEL_BLRP_MAX_QUEUE_SIZE,
                    scheduledDelayMillis: $env->int(Variables::OTEL_BLRP_SCHEDULE_DELAY) ?? Defaults::OTEL_BLRP_SCHEDULE_DELAY,
                    exportTimeoutMillis: $env->int(Variables::OTEL_BLRP_EXPORT_TIMEOUT) ?? Defaults::OTEL_BLRP_EXPORT_TIMEOUT,
                    maxExportBatchSize: $env->int(Variables::OTEL_BLRP_MAX_EXPORT_BATCH_SIZE) ?? Defaults::OTEL_BLRP_MAX_EXPORT_BATCH_SIZE,
                    autoFlush: true,
                    meterProvider: $context->meterProvider,
                ),
                'simple' => new SimpleLogRecordProcessor(
                    exporter: $registry->load(LogRecordExporterInterface::class, $logRecordExporter, $env, $context),
                ),
            });
        }
    }

    private static function loaderRegistry(): EnvComponentLoaderRegistry
    {
        $registry = new EnvComponentLoaderRegistry();
        foreach (ServiceLoader::load(EnvComponentLoader::class) as $loader) {
            $registry->register($loader);
        }

        /**
         * @psalm-suppress PossiblyNullFunctionCall,InaccessibleProperty
         * @phan-suppress PhanDeprecatedFunction,PhanAccessPropertyPrivate
         */
        (static function (EnvComponentLoaderRegistry $registry): void {
            foreach (Registry::$spanExporterFactories as $name => $_) { // @phpstan-ignore staticProperty.private
                try {
                    $registry->register(new SpanExporterLoaderSpanExporterFactory(Registry::spanExporterFactory($name), $name));
                } catch (LogicException) {
                }
            }
            foreach (Registry::$metricExporterFactories as $name => $_) { // @phpstan-ignore staticProperty.private
                try {
                    $registry->register(new MetricExporterLoaderMetricExporterFactory(Registry::metricExporterFactory($name), $name));
                } catch (LogicException) {
                }
            }
            foreach (Registry::$logRecordExporterFactories as $name => $_) { // @phpstan-ignore staticProperty.private
                try {
                    $registry->register(new LogRecordExporterLoaderLogRecordExporterFactory(Registry::logRecordExporterFactory($name), $name));
                } catch (LogicException) {
                }
            }

            foreach (Registry::$textMapPropagators as $name => $_) { // @phpstan-ignore staticProperty.private
                try {
                    $registry->register(new TextMapPropagatorLoaderTextMapPropagator(Registry::textMapPropagator($name), $name));
                } catch (LogicException) {
                }
            }
            foreach (Registry::$responsePropagators as $name => $_) { // @phpstan-ignore staticProperty.private
                try {
                    $registry->register(new ResponsePropagatorLoaderResponsePropagator(Registry::responsePropagator($name), $name));
                } catch (LogicException) {
                }
            }
            unset($_);
        })->bindTo(null, Registry::class)($registry);

        return $registry;
    }
}
