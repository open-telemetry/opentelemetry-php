<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv\Trace;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;

/**
 * @implements EnvComponentLoader<SamplerInterface>
 */
final class SamplerLoaderParentBasedAlwaysOff implements EnvComponentLoader
{
    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): SamplerInterface
    {
        return new ParentBased(new AlwaysOffSampler());
    }

    #[\Override]
    public function name(): string
    {
        return 'parentbased_always_off';
    }
}
