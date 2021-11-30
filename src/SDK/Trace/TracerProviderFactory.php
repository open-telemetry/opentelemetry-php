<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\GlobalLoggerHolder;

final class TracerProviderFactory
{
    private ExporterFactory $exporterFactory;
    private SamplerFactory $samplerFactory;
    private SpanProcessorFactory $spanProcessorFactory;

    public function __construct(
        string $name,
        ?ExporterFactory $exporterFactory = null,
        ?SamplerFactory $samplerFactory = null,
        ?SpanProcessorFactory $spanProcessorFactory = null
    ) {
        $this->exporterFactory = $exporterFactory ?: new ExporterFactory($name);
        $this->samplerFactory = $samplerFactory ?: new SamplerFactory();
        $this->spanProcessorFactory = $spanProcessorFactory ?: new SpanProcessorFactory();
    }

    public function create(): API\TracerProviderInterface
    {
        try {
            $exporter = $this->exporterFactory->fromEnvironment();
        } catch (\Throwable $t) {
            GlobalLoggerHolder::get()->warning('Unable to create exporter', ['error' => $t]);
            $exporter = null;
        }

        try {
            $sampler = $this->samplerFactory->fromEnvironment();
        } catch (\Throwable $t) {
            GlobalLoggerHolder::get()->warning('Unable to create sampler', ['error' => $t]);
            $sampler = null;
        }

        try {
            $spanProcessor = $this->spanProcessorFactory->fromEnvironment($exporter);
        } catch (\Throwable $t) {
            GlobalLoggerHolder::get()->warning('Unable to create span processor', ['error' => $t]);
            $spanProcessor = null;
        }

        return new TracerProvider(
            $spanProcessor,
            $sampler,
        );
    }
}
