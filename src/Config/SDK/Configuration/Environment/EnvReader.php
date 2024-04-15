<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

interface EnvReader
{

    public function read(string $name): ?string;
}
