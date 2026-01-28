<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv\Compatibility;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

/**
 * @implements EnvComponentLoader<SpanExporterInterface>
 */
final class SpanExporterLoaderSpanExporterFactory implements EnvComponentLoader
{
    public function __construct(
        private readonly SpanExporterFactoryInterface $spanExporterFactory,
        private readonly string $name,
    ) {
    }

    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): SpanExporterInterface
    {
        return $this->spanExporterFactory->create();
    }

    #[\Override]
    public function name(): string
    {
        return $this->name;
    }
}
