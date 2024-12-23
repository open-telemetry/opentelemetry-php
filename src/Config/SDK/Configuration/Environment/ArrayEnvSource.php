<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

final readonly class ArrayEnvSource implements EnvSource
{

    public function __construct(
        private array $env,
    ) {
    }

    public function readRaw(string $name): mixed
    {
        return $this->env[$name] ?? null;
    }
}
