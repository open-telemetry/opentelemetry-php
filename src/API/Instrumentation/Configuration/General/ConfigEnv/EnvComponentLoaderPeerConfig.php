<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\Configuration\General\ConfigEnv;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\PeerConfig;

/**
 * @implements EnvComponentLoader<GeneralInstrumentationConfiguration>
 */
final class EnvComponentLoaderPeerConfig implements EnvComponentLoader
{
    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): GeneralInstrumentationConfiguration
    {
        return new PeerConfig([]);
    }

    #[\Override]
    public function name(): string
    {
        return PeerConfig::class;
    }
}
