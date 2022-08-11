<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface MeterProviderInterface
{

    /**
     * Returns a `Meter` for the given instrumentation scope.
     *
     * @param string $name name of the instrumentation scope
     * @param string|null $version version of the instrumentation scope
     * @param string|null $schemaUrl schema url to record in the emitted telemetry
     * @param iterable<non-empty-string, string|bool|float|int|array|null> $attributes
     *        instrumentation scope attributes
     * @return MeterInterface meter instance for the instrumentation scope
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/api.md#get-a-meter
     */
    public function getMeter(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = []
    ): MeterInterface;
}
