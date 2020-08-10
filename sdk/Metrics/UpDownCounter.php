<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use OpenTelemetry\Metrics as API;

class UpDownCounter extends Counter implements API\UpDownCounter
{
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
        return API\MetricKind::UP_DOWN_COUNTER;
    }

    /**
     * Adds the specified value to the current counter's value
     *
     * @access	public
     * @return	self
     */
    public function add(int $value): API\Counter
    {
        $this->value += $value;

        return $this;
    }

    public function subtract(int $value) : API\UpDownCounter
    {
        $this->value -= $value;

        return $this;
    }

    public function decrement() : API\UpDownCounter
    {
        $this->value--;

        return $this;
    }
}
