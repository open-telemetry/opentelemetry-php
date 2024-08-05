<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use Closure;

class LateBindingMeterProvider implements MeterProviderInterface
{
    private ?MeterProviderInterface $meterProvider = null;

    /** @param Closure(): MeterProviderInterface $factory */
    public function __construct(
        private readonly Closure $factory,
    ) {
    }

    public function getMeter(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = []): MeterInterface
    {
        return $this->meterProvider?->getMeter($name, $version, $schemaUrl, $attributes)
            ?? new LateBindingMeter(fn (): MeterInterface => ($this->meterProvider ??= ($this->factory)())->getMeter($name, $version, $schemaUrl, $attributes));
    }
}
