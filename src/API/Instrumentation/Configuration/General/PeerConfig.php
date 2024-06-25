<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\Configuration\General;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;

class PeerConfig implements GeneralInstrumentationConfiguration
{
    public function __construct(public readonly array $config)
    {
    }
}
