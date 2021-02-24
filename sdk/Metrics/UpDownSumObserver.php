<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use InvalidArgumentException;
use OpenTelemetry\Metrics as API;

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
class UpDownSumObserver extends AbstractMetric implements API\UpDownSumObserver, API\LabelableMetric
{
    use HasLabels;
    use Spatie\Async\Process;
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
        return API\MetricKind::UP_DOWN_SUM_OBSERVER;
    }

    /**
     * Updates the UpDownSumObserver's value with the specified increment then returns the current value.
     *
     * @access	public
     *
     * @param int|float $increment, accepts INTs or FLOATs. If increment is a float, it is truncated.
     *
     * @return int $value
     */

    protected $pool = Pool::create();

    $pool[] = public observe(function ()  use ($value) {
        if (is_float($value)) {
            /*
            *
            * todo: send the following message to the log when logger is implemented:
            *       Floating point detected, ignoring the fractional decimal places.
            */
            $value = (int) $value;
        }
        if (!is_int($value)) {
            throw new InvalidArgumentException('Only numerical values can be used to update the UpDownCounter.');
        }
        $this->value += $value;

        return $this->$value;
    })->then(function () {
        return $this->value;
    });

    await($pool);
}
