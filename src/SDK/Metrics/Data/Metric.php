<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class Metric
{
    public function __construct(
        /**
         * @readonly
         */
        public InstrumentationScopeInterface $instrumentationScope,
        /**
         * @readonly
         */
        public ResourceInfo $resource,
        /**
         * @readonly
         */
        public string $name,
        /**
         * @readonly
         */
        public ?string $unit,
        /**
         * @readonly
         */
        public ?string $description,
        /**
         * @readonly
         */
        public DataInterface $data
    ) {
    }
}
