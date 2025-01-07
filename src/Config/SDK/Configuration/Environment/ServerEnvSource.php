<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

final class ServerEnvSource implements EnvSource
{
    public function readRaw(string $name): mixed
    {
        return $_SERVER[$name] ?? null;
    }
}
