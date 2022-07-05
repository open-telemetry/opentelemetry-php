<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Resource;

final class Metric {

    public function __construct(
        public readonly InstrumentationScope $instrumentationScope,
        public readonly Resource $resource,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $unit,
        public readonly Data $data,
    ) {}
}
