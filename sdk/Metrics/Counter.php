<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use InvalidArgumentException;
use OpenTelemetry\Metrics as API;

class Counter extends AbstractMetric implements API\Counter, API\LabelableMetric
{
    use HasLabels;

    /**
     * @var int $value
     */
    protected $value = 0;

    /**
     * Get $type
     *
     * @return  int
     */
    public function getType(): int
    {
        return API\MetricKind::COUNTER;
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
     * Increments the current value
     *
     * @access	public
     * @return	self
     */
    public function increment(): API\Counter
    {
        $this->value++;

        return $this;
    }

    /**
     * Adds the specified value to the current counter's value
     *
     * @access	public
     * @return	self
     */
    public function add(int $value): API\Counter
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Only positive numbers can be added to the Counter');
        }

        $this->value += $value;

        return $this;
    }
}
