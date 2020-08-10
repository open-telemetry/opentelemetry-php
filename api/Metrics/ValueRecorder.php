<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface ValueRecorder
{
    /**
     * record.
     *
     * @access	public
     * @param int|float $value
     * @return void
     */
    public function record($value) : void;
}
