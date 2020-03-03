<?php

declare(strict_types=1);

namespace OpenTelemetry\Propagation;

interface Setter
{
    /**
     * Set the value for a given key on the associated carrier.
     *
     * @param $carrier
     * @param string $key
     * @param string $value
     * @return void
     */
    public function set($carrier, string $key, string $value) : void;
}
