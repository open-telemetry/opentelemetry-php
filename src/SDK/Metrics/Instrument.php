<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\MeterInterface;

final class Instrument
{
    public function __construct(
        public readonly string|InstrumentType $type,
        public readonly string $name,
        public readonly ?string $unit,
        public readonly ?string $description,
        public readonly array $advisory = [],
        public readonly ?MeterInterface $meter = null,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->meter?->isEnabled() ?? true;
    }
}
