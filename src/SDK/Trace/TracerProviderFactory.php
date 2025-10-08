<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Sdk;

final class TracerProviderFactory
{
    use LogsMessagesTrait;

    public function __construct(
        private readonly ComponentProviderBasedExporterFactory $exporterFactory = new ComponentProviderBasedExporterFactory(),
        private readonly ComponentProviderBasedSamplerFactory $samplerFactory = new ComponentProviderBasedSamplerFactory(),
        private readonly ComponentProviderBasedSpanProcessorFactory $spanProcessorFactory = new ComponentProviderBasedSpanProcessorFactory(),
    ) {}

    public function create(): TracerProviderInterface
    {
        if (Sdk::isDisabled()) {
            return new NoopTracerProvider();
        }

        try {
            $exporter = $this->exporterFactory->create();
        } catch (\Throwable $t) {
            self::logWarning('Unable to create exporter', ['exception' => $t]);
            $exporter = null;
        }

        try {
            $sampler = $this->samplerFactory->create();
        } catch (\Throwable $t) {
            self::logWarning('Unable to create sampler', ['exception' => $t]);
            $sampler = null;
        }

        try {
            $spanProcessor = $this->spanProcessorFactory->create($exporter);
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
