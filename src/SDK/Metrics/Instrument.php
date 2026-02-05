<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final class Instrument
{
    public function __construct(
        public readonly string|InstrumentType $type,
        public readonly string $name,
        public readonly ?string $unit,
        public readonly ?string $description,
        public readonly array $advisory = [],
    ) {
    }
}
