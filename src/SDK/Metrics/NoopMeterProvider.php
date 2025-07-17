<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;

class NoopMeterProvider implements MeterProviderInterface
{
    #[\Override]
    public function shutdown(): bool
    {
        return true;
    }

    #[\Override]
    public function forceFlush(): bool
    {
        return true;
    }

    #[\Override]
    public function getMeter(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = []): MeterInterface
    {
        return new NoopMeter();
    }

    #[\Override]
    public function updateConfigurator(Configurator $configurator): void
    {
        // no-op
    }
}
