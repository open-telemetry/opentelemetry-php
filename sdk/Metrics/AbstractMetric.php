<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use OpenTelemetry\Metrics as API;

abstract class AbstractMetric implements API\Metric
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $description
     */
    protected $description;

    public function __construct(string $name, string $description = '')
    {
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
