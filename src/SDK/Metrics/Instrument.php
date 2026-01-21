<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final readonly class Instrument
{
    public function __construct(
        public string|InstrumentType $type,
        public string $name,
        public ?string $unit,
        public ?string $description,
        public array $advisory = [],
    ) {
    }
}
