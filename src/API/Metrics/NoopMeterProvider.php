<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use Exception;

class NoopMeterProvider implements MeterProviderInterface
{
    public function getMeter(string $name, ?string $version = null): MeterInterface
    {
        throw new Exception('Not implemented yet');
    }
}
