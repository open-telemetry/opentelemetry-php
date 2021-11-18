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

    public function create(): API\TracerProviderInterface
    {
        $exporter = $this->exporterFactory->fromEnvironment();
        $sampler = $this->samplerFactory->fromEnvironment();
        $spanProcessor = $this->spanProcessorFactory->fromEnvironment($exporter);

        return new TracerProvider(
            $spanProcessor,
            $sampler,
        );
    }
}
