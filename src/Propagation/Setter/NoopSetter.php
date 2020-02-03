<?php

declare(strict_types=1);

namespace OpenTelemetry\Propagation\Setter;

final class NoopSetter implements SetterInterface
{
    /**
     * Set the value for a given key on the associated carrier.
     *
     * @param mixed $carrier
     * @param string $key
     * @param string $value
     */
    public function put($carrier, string $key, string $value) : void
    {
        // NOOP
    }
}
