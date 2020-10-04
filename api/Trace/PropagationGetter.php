<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface PropagationGetter
{
    /**
     * Gets the value of a given key from a carrier.
     *
     * @param mixed  $carrier
     * @param string $key
     * @return string|null
     */
    public function get($carrier, string $key) : ?string;
}
