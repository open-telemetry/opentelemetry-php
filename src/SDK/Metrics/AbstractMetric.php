<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

abstract class AbstractMetric implements API\MetricInterface
{
    protected string $name;

    protected string $description;

    protected ResourceInfo $resource;

    protected InstrumentationScopeInterface $instrumentationScope;

    protected int $startEpochNanos;

    protected int $epochNanos;

    public function __construct(
        string $name,
        string $description = '',
        ?ResourceInfo $resource = null,
        ?InstrumentationScopeInterface $instrumentationScope = null,
        int $startEpochNanos = 0
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->resource = $resource ?? ResourceInfoFactory::defaultResource();
        $this->instrumentationScope = $instrumentationScope ?? new InstrumentationScope($name);
        $this->startEpochNanos = $startEpochNanos ?: ClockFactory::getDefault()->now();

        $this->updateEpochNanos($this->startEpochNanos);
    }

    protected function updateEpochNanos(int $timestamp = 0): void
    {
        $this->epochNanos = $timestamp ?: ClockFactory::getDefault()->now();
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

    /**
     * {@inheritDoc}
     */
    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    /**
     * {@inheritDoc}
     */
    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->instrumentationScope;
    }

    /**
     * {@inheritDoc}
     */
    public function getStartEpochNanos(): int
    {
        return $this->startEpochNanos;
    }

    /**
     * {@inheritDoc}
     */
    public function getEpochNanos(): int
    {
        return $this->epochNanos;
    }
}
