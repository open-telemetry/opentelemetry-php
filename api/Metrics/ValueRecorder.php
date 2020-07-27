<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface ValueRecorder
{
    /**
     * records the given value to this ValueRecorder.
     *
     * @access	public
     * @param int|float $value
     * @return void
     */
    public function record($value) : void;
}
