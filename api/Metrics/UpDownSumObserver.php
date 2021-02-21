<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

/*
 * Name: UpDownSumObserver
 * Instrument kind : Asynchronous additive
 * Function(argument) : Observe(sum) where sum is a numeric value
 * Default aggregation : Sum
 * Notes : Captures only one value per Measurement Interval, part of a non-monotonic sum
 *
 * UpDownSumObserver is the asynchronous instrument corresponding to UpDownCounter,
 * used to capture a non-monotonic count with Observe(sum).
 * "Sum" appears in the name to remind users that it is used to capture sums directly.
 * Use a UpDownSumObserver to capture any value that starts at zero and rises
 * or falls throughout the process lifetime.
 *
 */

interface UpDownSumObserver
{
    /**
     * Updates sum value with the positive or negative int that is passed in.
     *
     * @access	public
     * @param	int|float $value
     * @return	int returns the non-monotonic sum
     */
    public function observe($value) : int;
}
