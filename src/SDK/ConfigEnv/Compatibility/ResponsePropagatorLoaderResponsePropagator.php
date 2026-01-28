<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv\Compatibility;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;

/**
 * @implements EnvComponentLoader<ResponsePropagatorInterface>
 */
final class ResponsePropagatorLoaderResponsePropagator implements EnvComponentLoader
{
    public function __construct(
        private readonly ResponsePropagatorInterface $responsePropagator,
        private readonly string $name,
    ) {
    }

    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): ResponsePropagatorInterface
    {
        return $this->responsePropagator;
    }

    #[\Override]
    public function name(): string
    {
        return $this->name;
    }
}
