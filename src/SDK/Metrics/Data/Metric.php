<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Resource;

final class Metric
{
    /**
     * @readonly
     */
    public InstrumentationScope $instrumentationScope;
    /**
     * @readonly
     */
    public Resource $resource;
    /**
     * @readonly
     */
    public string $name;
    /**
     * @readonly
     */
    public ?string $description;
    /**
     * @readonly
     */
    public ?string $unit;
    /**
     * @readonly
     */
    public Data $data;
    public function __construct(InstrumentationScope $instrumentationScope, Resource $resource, string $name, ?string $description, ?string $unit, Data $data)
    {
        $this->instrumentationScope = $instrumentationScope;
        $this->resource = $resource;
        $this->name = $name;
        $this->description = $description;
        $this->unit = $unit;
        $this->data = $data;
    }
}
