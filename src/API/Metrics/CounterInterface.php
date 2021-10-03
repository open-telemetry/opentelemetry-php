<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface CounterInterface extends MetricInterface
{
    /**
     * Adds value to the counter
     *
     * @access	public
     * @param	int	$value
     * @return	self
     */
    public function add(int $value): CounterInterface;

    /**
     * Increments value
     *
     * @access	public
     * @return	self
     */
    public function increment(): CounterInterface;

    /**
     * Gets the value
     *
     * @access	public
     * @return	int
     */
    public function getValue(): int;
}
