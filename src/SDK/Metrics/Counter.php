<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use InvalidArgumentException;
use OpenTelemetry\API\Metrics as API;

class Counter extends AbstractMetric implements API\CounterInterface, API\LabelableMetricInterfaceInterface
{
    use HasLabelsTrait;

    protected int $value = 0;

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
    public function increment(): API\CounterInterface
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
    public function add(int $value): API\CounterInterface
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Only positive numbers can be added to the Counter');
        }

        $this->value += $value;

        return $this;
    }
}
