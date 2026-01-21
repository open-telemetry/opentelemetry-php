<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final readonly class Metric
{
    public function __construct(
        public InstrumentationScopeInterface $instrumentationScope,
        public ResourceInfo $resource,
        public string $name,
        public ?string $unit,
        public ?string $description,
        public DataInterface $data,
    ) {
    }
}
