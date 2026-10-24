<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

use function getenv;

final class GetenvEnvSource implements EnvSource
{
    #[\Override]
    public function readRaw(string $name): string|false
    {
        return getenv($name);
    }
}
