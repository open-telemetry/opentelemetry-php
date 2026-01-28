<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv\Trace;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;

/**
 * @implements EnvComponentLoader<SamplerInterface>
 */
final class SamplerLoaderAlwaysOn implements EnvComponentLoader
{
    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): SamplerInterface
    {
        return new AlwaysOnSampler();
    }

    #[\Override]
    public function name(): string
    {
        return 'always_on';
    }
}
