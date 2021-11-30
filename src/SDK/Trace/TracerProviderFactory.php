<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;

final class TracerProviderFactory
{
    private ExporterFactory $exporterFactory;
    private SamplerFactory $samplerFactory;

    public function __construct(
        string $name,
        ?ExporterFactory $exporterFactory = null,
        ?SamplerFactory $samplerFactory = null
    ) {
        $this->exporterFactory = $exporterFactory ?: new ExporterFactory($name);
        $this->samplerFactory = $samplerFactory ?: new SamplerFactory();
    }

    public function create(): API\TracerProviderInterface
    {
        $exporter = $this->exporterFactory->fromEnvironment();
        $sampler = $this->samplerFactory->fromEnvironment();

        return new TracerProvider(
            new BatchSpanProcessor($exporter),
            $sampler,
        );
    }
}
