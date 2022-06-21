<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class Meter implements API\MeterInterface
{
    protected ResourceInfo $resource;
    protected InstrumentationScopeInterface $instrumentationScope;
    protected int $startEpochNanos = 0;

    public function __construct(
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope
    ) {
        $this->resource = $resource;
        $this->instrumentationScope = $instrumentationScope;
    }

    public function setStartTimestamp(int $timestamp): API\MeterInterface
    {
        if (0 > $timestamp) {
            return $this;
        }

        $this->startEpochNanos = $timestamp;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->instrumentationScope;
    }

    /**
     * {@inheritdoc}
     */
    public function newCounter(string $name, string $description = ''): API\CounterInterface
    {
        return new Counter(
            $name,
            $description,
            $this->resource,
            $this->instrumentationScope,
            $this->startEpochNanos
        );
    }

    /**
     * {@inheritdoc}
     */
    public function newUpDownCounter(string $name, string $description = ''): API\UpDownCounterInterface
    {
        return new UpDownCounter(
            $name,
            $description,
            $this->resource,
            $this->instrumentationScope,
            $this->startEpochNanos
        );
    }

    /**
     * {@inheritdoc}
     */
    public function newValueRecorder(string $name, string $description = ''): API\ValueRecorderInterface
    {
        return new ValueRecorder(
            $name,
            $description,
            $this->resource,
            $this->instrumentationScope,
            $this->startEpochNanos
        );
    }
}
