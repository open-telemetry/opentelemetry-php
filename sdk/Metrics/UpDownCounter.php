<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use InvalidArgumentException;
use OpenTelemetry\Metrics as API;

/*
 * Name: UpDownCounter
 * Instrument kind : Synchronous additive
 * Function(argument) : add(increment)
 * Default aggregation : Sum
 * Notes : Per-request, part of a non-monotonic sum
 *
 * UpDownCounter supports negative increments. This makes UpDownCounter
 * not useful for computing a rate aggregation. It aggregates a Sum,
 * only the sum is non-monotonic. It is generally useful for capturing changes
 * in an amount of resources used, or any quantity that rises and falls during
 * a request.
 */
class UpDownCounter extends AbstractMetric implements API\UpDownCounter, API\LabelableMetric
{
    use HasLabels;

    /**
     * @var int $value
     */
    protected $value = 0;

    /**
     * getType
     *
     * @access	public
     * @return	int
     */
    public function getType(): int
    {
        return API\MetricKind::UP_DOWN_COUNTER;
    }

    /**
     * Returns the current value
     *
     * @access	public
     * @return	int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Updates the UpDownCounter's value with the specified increment then returns the current value.
     *
     * @access	public
     *
     * @param int|float $increment, accepts INTs or FLOATs. If increment is a float, it is truncated.
     *
     * @return int $value
     */

    public function add($increment): int
    {
        if (is_float($increment)) {
            /*
             *
             * todo: send the following message to the log when logger is implemented:
             *       Floating point detected, ignoring the fractional decimal places.
             */
            $increment = (int) $increment;
        }
        if (!is_int($increment)) {
            throw new InvalidArgumentException('Only numerical values can be used to update the UpDownCounter.');
        }
        $this->value += $increment;

        return $this->value;
    }
}
