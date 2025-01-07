<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

final class ArrayEnvSource implements EnvSource
{
    public function __construct(
        private readonly array $env,
    ) {
    }

    public function readRaw(string $name): mixed
    {
        return $this->env[$name] ?? null;
    }
}
