<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

interface EnvSourceProvider
{
    public function getEnvSource(): EnvSource;
}
