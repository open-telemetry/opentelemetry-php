<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

interface EnvSource
{
    public function readRaw(string $name): mixed;
}
