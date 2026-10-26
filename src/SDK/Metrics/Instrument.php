<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final class Instrument
{
    public readonly string $unit;
    public readonly string $description;

    /**
     * A null unit or description is stored as an empty string. The spec treats them as the same.
     */
    public function __construct(
        public readonly string|InstrumentType $type,
        public readonly string $name,
        ?string $unit,
        ?string $description,
        public readonly array $advisory = [],
    ) {
        $this->unit = $unit ?? '';
        $this->description = $description ?? '';
    }
}
