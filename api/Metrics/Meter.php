<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface Meter
{
    /**
     * Returns the meter name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the meter version.
     *
     * @return string Metric version
     */
    public function getVersion(): string;

    /**
     * Creates a Counter metric instrument.
     *
     * @param string $name        (required) - Counter name
     * @param string $description (optional) - Counter description
     *
     * @return Counter
     */
    public function newCounter(string $name, string $description): Counter;

    /**
     * Creates an UpDownCounter metric instrument.
     *
     * @param string $name        (required) - UpDownCounter name
     * @param string $description (optional) - UpDownCounter description
     *
     * @return UpDownCounter
     */
    public function newUpDownCounter(string $name, string $description): UpDownCounter;

    /**
     * Creates a ValueRecorder metric instrument.
     *
     * @param string $name        (required) - ValueRecorder name
     * @param string $description (optional) - ValueRecorder description
     *
     * @return ValueRecorder
     */
    public function newValueRecorder(string $name, string $description): ValueRecorder;
}
