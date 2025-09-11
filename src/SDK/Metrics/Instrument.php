<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

final class Instrument
{
    public function __construct(
        public readonly InstrumentType $type,
        public readonly string $name,
        public readonly ?string $unit,
        public readonly ?string $description,
        public readonly array $advisory = [],
        public readonly ?Temporality $temporality = null,
    ) {
    }
}
