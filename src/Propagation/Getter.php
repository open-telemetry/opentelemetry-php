<?php

declare(strict_types=1);

namespace OpenTelemetry\Propagation;

interface Getter
{
    /**
     * Gets the value of a given key on a carrier.
     *
     * @param string $key
     * @return string|null
     */
    public function get(string $key) : ?string;
}
