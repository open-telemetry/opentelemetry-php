<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#setter-argument
 */
interface PropagationSetterInterface
{
    /**
     * Set the value for a given key on the associated carrier.
     */
    public function set(&$carrier, string $key, string $value) : void;
}
