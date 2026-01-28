<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv\Compatibility;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

/**
 * @implements EnvComponentLoader<LogRecordExporterInterface>
 */
final class LogRecordExporterLoaderLogRecordExporterFactory implements EnvComponentLoader
{
    public function __construct(
        private readonly LogRecordExporterFactoryInterface $logRecordExporterFactory,
        private readonly string $name,
    ) {
    }

    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): LogRecordExporterInterface
    {
        return $this->logRecordExporterFactory->create();
    }

    #[\Override]
    public function name(): string
    {
        return $this->name;
    }
}
