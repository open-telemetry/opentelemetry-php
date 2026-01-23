<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\ConfigEnv\Compatibility;

use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoader;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvComponentLoaderRegistry;
use OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * @implements EnvComponentLoader<TextMapPropagatorInterface>
 */
final class TextMapPropagatorLoaderTextMapPropagator implements EnvComponentLoader
{
    public function __construct(
        private readonly TextMapPropagatorInterface $textMapPropagator,
        private readonly string $name,
    ) {
    }

    #[\Override]
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): TextMapPropagatorInterface
    {
        return $this->textMapPropagator;
    }

    #[\Override]
    public function name(): string
    {
        return $this->name;
    }
}
