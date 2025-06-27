<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\SpanExporter\NoopSpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SpanProcessorContext;

final class TracerProviderFactory
{
    use LogsMessagesTrait;

    public function __construct(
        private readonly ExporterFactory $exporterFactory = new ExporterFactory(),
        private readonly SamplerFactory $samplerFactory = new SamplerFactory(),
        private readonly SpanProcessorFactory $spanProcessorFactory = new SpanProcessorFactory(),
        private readonly MeterProviderInterface $meterProvider = new NoopMeterProvider(),
    ) {
    }

    public function create(): TracerProviderInterface
    {
        if (Sdk::isDisabled()) {
            return new NoopTracerProvider();
        }

        try {
            $exporter = $this->exporterFactory->create();
        } catch (\Throwable $t) {
            self::logWarning('Unable to create exporter', ['exception' => $t]);
            $exporter = new NoopSpanExporter();
        }

        try {
            $sampler = $this->samplerFactory->create();
        } catch (\Throwable $t) {
            self::logWarning('Unable to create sampler', ['exception' => $t]);
            $sampler = null;
        }

        try {
            $spanProcessor = $this->spanProcessorFactory->create(
                new SpanProcessorContext(
                    $this->meterProvider,
                    $exporter,
                    Configuration::getBoolean(Variables::OTEL_PHP_INTERNAL_METRICS_ENABLED, false)
                )
            );
        } catch (\Throwable $t) {
            self::logWarning('Unable to create span processor', ['exception' => $t]);
            $spanProcessor = null;
        }

        return new TracerProvider(
            $spanProcessor,
            $sampler,
        );
    }
}
