<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource;

final class Metric {

    public function __construct(
        public readonly InstrumentationLibrary $instrumentationLibrary,
        public readonly Resource $resource,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $unit,
        public readonly Data $data,
    ) {}
}
