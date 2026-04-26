<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Common\Configuration\Defaults;
use OpenTelemetry\SDK\Common\Configuration\EnvComponentLoaderRegistry;
use OpenTelemetry\SDK\Common\Configuration\EnvResolver;
use OpenTelemetry\SDK\Common\Configuration\Variables;

/**
 * TODO deprecated
 */
class SamplerFactory
{
    /**
     * @phan-suppress PhanTypeMismatchReturn
     */
    public function create(): SamplerInterface
    {
        $registry = new EnvComponentLoaderRegistry();
        foreach (ServiceLoader::load(EnvComponentLoader::class) as $loader) {
            $registry->register($loader);
        }

        $env = new EnvResolver();
        $context = new Context();

        $samplerName = $env->string(Variables::OTEL_TRACES_SAMPLER) ?? Defaults::OTEL_TRACES_SAMPLER;

        return $registry->load(SamplerInterface::class, $samplerName, $env, $context);
    }
}
