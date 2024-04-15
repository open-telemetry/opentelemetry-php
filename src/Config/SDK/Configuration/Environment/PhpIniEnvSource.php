<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

use function get_cfg_var;

final class PhpIniEnvSource implements EnvSource
{

    public function readRaw(string $name): string|array|false
    {
        return get_cfg_var($name);
    }
}
