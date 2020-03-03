<?php

declare(strict_types=1);

namespace OpenTelemetry\Propagation;

interface Getter
{
    /**
     * Gets the value of a given key from a carrier.
     *
     * @param $carrier
     * @param string $key
     * @return string|null
     */
    public function get($carrier, string $key) : ?string;
}
