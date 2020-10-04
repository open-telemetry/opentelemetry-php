<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

/*
 * Name: UpDownCounter
 * Instrument kind : Synchronous additive
 * Function(argument) : Add(increment) where increment is a numeric value
 * Default aggregation : Sum
 * Notes : Per-request, part of a non-monotonic sum
 *
 * UpDownCounter supports negative increments. This makes UpDownCounter
 * not useful for computing a rate aggregation. It aggregates a Sum,
 * only the sum is non-monotonic. It is generally useful for capturing changes
 * in an amount of resources used, or any quantity that rises and falls during
 * a request.
 *
 */
interface UpDownCounter
{
    /**
     * Updates counter value with the positive or negative int that is passed in.
     *
     * @access	public
     * @param	int|float $increment
     * @return	int returns the non-monotonic sum
     */
    public function add($increment) : int;
}
