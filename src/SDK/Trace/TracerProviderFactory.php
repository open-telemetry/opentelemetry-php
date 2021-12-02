<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;

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

    public function fromConfig(object $config): API\TracerProviderInterface
    {
        $exporter = $this->exporterFactory->fromConfig($config);
        $sampler = $this->samplerFactory->fromConfig($config);
        $spanProcessor = $this->spanProcessorFactory->fromConfig($config, $exporter);

        return new TracerProvider(
            $spanProcessor,
            $sampler,
        );
    }
}
