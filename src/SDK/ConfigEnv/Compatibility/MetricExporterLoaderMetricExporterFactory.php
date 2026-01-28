<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv\Compatibility;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

/**
 * @implements EnvComponentLoader<MetricExporterInterface>
 */
final class MetricExporterLoaderMetricExporterFactory implements EnvComponentLoader
{
    public function __construct(
        private readonly MetricExporterFactoryInterface $metricExporterFactory,
        private readonly string $name,
    ) {
    }

    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): MetricExporterInterface
    {
        return $this->metricExporterFactory->create();
    }

    #[\Override]
    public function name(): string
    {
        return $this->name;
    }
}
