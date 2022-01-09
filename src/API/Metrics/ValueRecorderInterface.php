<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface ValueRecorderInterface extends MetricInterface
{
    /**
     * Records the given value to this ValueRecorder.
     *
     * @access	public
     */
    public function record(float $value) : void;

    /**
     * Returns the sum of the values
     *
     * @return	float
     */
    public function getSum(): float;

    /**
     * Returns the min of the values
     *
     * @access	public
     */
    public function getMin(): float;

    /**
     * Returns the max of the values
     *
     * @access	public
     */
    public function getMax(): float;

    /**
     * Returns the count of the values
     *
     * @access	public
     */
    public function getCount(): int;
}
