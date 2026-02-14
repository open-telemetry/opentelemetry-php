<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv\Trace;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;

/**
 * @implements EnvComponentLoader<SamplerInterface>
 */
final class SamplerLoaderTraceIdRatio implements EnvComponentLoader
{
    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): SamplerInterface
    {
        return new TraceIdRatioBasedSampler($env->numeric(Variables::OTEL_TRACES_SAMPLER_ARG, max: 1) ?? 1.);
    }

    #[\Override]
    public function name(): string
    {
        return 'traceidratio';
    }
}
