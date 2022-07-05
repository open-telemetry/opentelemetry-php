<?php declare(strict_types=1);
namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\Meter;
use OpenTelemetry\API\Metrics\MeterProvider;

/**
 * @internal
 */
final class NoopMeterProvider implements MeterProvider {

    public function getMeter(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [],
    ): Meter {
        return new NoopMeter();
    }
}
