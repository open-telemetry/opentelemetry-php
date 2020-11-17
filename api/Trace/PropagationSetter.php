<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface PropagationSetter
{
    /**
     * Set the value for a given key on the associated carrier.
     *
     * @param mixed  $carrier
     * @param string $key
     * @param string $value
     * @return void
     */
    public function set(&$carrier, string $key, string $value) : void;
}
