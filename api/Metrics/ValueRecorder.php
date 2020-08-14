<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface ValueRecorder extends Metric
{
    /**
     * records the given value to this ValueRecorder.
     *
     * @access	public
     * @param int|float $value
     * @return void
     */
    public function record($value) : void;

    /**
     * Returns the sum of the values
     *
     * @access	public
     * @return	float
     */
    public function getSum(): float;

    /**
     * Returns the min of the values
     *
     * @access	public
     * @return	float
     */
    public function getMin(): float;

    /**
     * Returns the max of the values
     *
     * @access	public
     * @return	float
     */
    public function getMax(): float;

    /**
     * Returns the count of the values
     *
     * @access	public
     * @return	int
     */
    public function getCount(): int;
}
