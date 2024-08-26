<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration\Noop;

use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Configuration\ConfigProviderInterface;

class NoopConfigProvider implements ConfigProviderInterface
{
    public function getInstrumentationConfig(): ConfigProperties
    {
        return new NoopConfigProperties();
    }
}
