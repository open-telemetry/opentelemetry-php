<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

interface MeterInterface
{
    /**
     * Returns the meter's resource info
     *
     * @return ResourceInfo
     */
    public function getResource(): ResourceInfo;

    /**
     * Returns meter's instrumentation scope
     *
     * @return InstrumentationScopeInterface
     */
    public function getInstrumentationScope(): InstrumentationScopeInterface;

    /**
     * Creates a Counter metric instrument.
     *
     * @param string $name        (required) - Counter name
     * @param string $description (optional) - Counter description
     *
     * @return CounterInterface
     */
    public function newCounter(string $name, string $description): CounterInterface;

    /**
     * Creates an UpDownCounter metric instrument.
     *
     * @param string $name        (required) - UpDownCounter name
     * @param string $description (optional) - UpDownCounter description
     *
     * @return UpDownCounterInterface
     */
    public function newUpDownCounter(string $name, string $description): UpDownCounterInterface;

    /**
     * Creates a ValueRecorder metric instrument.
     *
     * @param string $name        (required) - ValueRecorder name
     * @param string $description (optional) - ValueRecorder description
     *
     * @return ValueRecorderInterface
     */
    public function newValueRecorder(string $name, string $description): ValueRecorderInterface;
}
