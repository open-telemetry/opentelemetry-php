<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use InvalidArgumentException;
use OpenTelemetry\API\Metrics as API;

/*
 * Name	: ValueRecorder
 * Instrument kind : Synchronous
 * Function(argument) : record(value)
 * Default aggregation : MinMaxSumCount
 * Notes : Per-request, any non-additive measurement
 *
 * ValueRecorder is a non-additive synchronous instrument useful for
 * recording any non-additive number, positive or negative. Values
 * captured by a Record(value) are treated as individual events
 * belonging to a distribution that is being summarized. ValueRecorder
 * should be chosen either when capturing measurements that do not
 * contribute meaningfully to a sum, or when capturing numbers that
 * are additive in nature, but where the distribution of individual
 * increments is considered interesting.
 *
 * Example: One of the most common uses for ValueRecorder is to capture latency
 * measurements. Latency measurements are not additive in the sense that
 * there is little need to know the latency-sum of all processed requests.
 * We use a ValueRecorder instrument to capture latency measurements
 * typically because we are interested in knowing mean, median, and other
 * summary statistics about individual events.
 *
 * The default aggregation for ValueRecorder computes the minimum and maximum
 * values, the sum of event values, and the count of events, allowing the rate,
 * the mean, and range of input values to be monitored.
 */
class ValueRecorder extends AbstractMetric implements API\ValueRecorderInterface, API\LabelableMetricInterfaceInterface
{
    use HasLabelsTrait;

    protected float $valueSum = 0;

    protected float $valueMin = INF;

    protected float $valueMax = -INF;

    protected int $valueCount = 0;

    /*
     * Testing floating point values for equality is problematic, due to
     * the way that they are represented internally.  Therefore, force all
     * precision to a predetermined number, and equality can be determined.
     */
    private int $decimalPointPrecision = 10;

    /**
     * getType: get the type of metric instrument
     */
    public function getType(): int
    {
        return API\MetricKind::VALUE_RECORDER;
    }

    /**
     * Returns the sum of the values
     *
     * @access	public
     */
    public function getSum(): float
    {
        return $this->valueSum;
    }

    /**
     * Returns the min of the values
     *
     * @access	public
     */
    public function getMin(): float
    {
        return $this->valueMin;
    }

    /**
     * Returns the max of the values
     *
     * @access	public
     */
    public function getMax(): float
    {
        return $this->valueMax;
    }

    /**
     * Returns the mean of the values
     *
     * @access	public
     */
    public function getMean(): float
    {
        if (0 == $this->valueCount) {
            return 0;
        }

        return ($this->valueSum/$this->valueCount);
    }

    /**
     * Returns the count of the values
     *
     * @access	public
     */
    public function getCount(): int
    {
        return $this->valueCount;
    }

    /**
     * Updates the ValueRecorder's value with the specified value.
     *
     * @access	public
     */
    public function record(float $value): void
    {
        $value = round($value, $this->decimalPointPrecision);

        $this->valueSum += $value;
        $this->valueMin = min($this->valueMin, $value);
        $this->valueMax = max($this->valueMax, $value);
        $this->valueCount++;
    }
}
