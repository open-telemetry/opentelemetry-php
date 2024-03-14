<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class Metric
{
    public function __construct(
        public readonly InstrumentationScopeInterface $instrumentationScope,
        public readonly ResourceInfo $resource,
        public readonly string $name,
        public readonly ?string $unit,
        public readonly ?string $description,
        public readonly DataInterface $data,
    ) {
    }
}
