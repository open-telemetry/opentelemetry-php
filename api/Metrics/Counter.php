<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface Counter extends Metric
{
    /**
     * Adds value to the counter
     *
     * @access	public
     * @param	int	$value
     * @return	self
     */
    public function add(int $value): Counter;

    /**
     * Increments value
     *
     * @access	public
     * @return	self
     */
    public function increment(): Counter;

    /**
     * Gets the value
     *
     * @access	public
     * @return	int
     */
    public function getValue(): int;
}
