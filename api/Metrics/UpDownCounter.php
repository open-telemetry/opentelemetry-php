<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface UpDownCounter extends Counter
{
    /**
     * Subtracts counter value
     *
     * @access	public
     * @param	int	$value	
     * @return	UpDownCounter
     */
    public function subtract(int $value) : UpDownCounter;

    /**
     * Decrements counter value
     *
     * @access	public
     * @return	UpDownCounter
     */
    public function decrement() : UpDownCounter;
}
