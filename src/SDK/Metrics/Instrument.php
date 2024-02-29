<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final class Instrument
{
    public function __construct(
        /** @readonly */
        public string|InstrumentType $type,
        /** @readonly */
        public string $name,
        /** @readonly */
        public ?string $unit,
        /** @readonly */
        public ?string $description,
        /** @readonly */
        public array $advisory = []
    ) {
    }
}
