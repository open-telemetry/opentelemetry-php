<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv\Trace;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;

/**
 * @implements EnvComponentLoader<SamplerInterface>
 */
final class SamplerLoaderAlwaysOff implements EnvComponentLoader
{
    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): SamplerInterface
    {
        return new AlwaysOffSampler();
    }

    #[\Override]
    public function name(): string
    {
        return 'always_off';
    }
}
