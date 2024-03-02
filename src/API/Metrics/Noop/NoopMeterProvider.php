<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;

final class NoopMeterProvider implements MeterProviderInterface
{
    public function getMeter(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [],
    ): MeterInterface {
        return new NoopMeter();
    }
}
