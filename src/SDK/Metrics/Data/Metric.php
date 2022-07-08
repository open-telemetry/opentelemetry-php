<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class Metric
{
    /**
     * @readonly
     */
    public InstrumentationScopeInterface $instrumentationScope;
    /**
     * @readonly
     */
    public ResourceInfo $resource;
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
    public DataInterface $data;

    public function __construct(InstrumentationScopeInterface $instrumentationScope, ResourceInfo $resource, string $name, ?string $unit, ?string $description, DataInterface $data)
    {
        $this->instrumentationScope = $instrumentationScope;
        $this->resource = $resource;
        $this->name = $name;
        $this->description = $description;
        $this->unit = $unit;
        $this->data = $data;
    }
}
