<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration\Noop;

use OpenTelemetry\API\Configuration\ConfigProperties;

class NoopConfigProperties implements ConfigProperties
{
    public function get(string $id): mixed
    {
        return null;
    }
}
