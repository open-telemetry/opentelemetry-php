<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#getter-argument
 */
interface PropagationGetter
{
    /**
     * Returns the list of all the keys in the carrier.
     *
     * @return iterable<string>
     */
    public function keys($carrier): iterable;

    /**
     * Gets the value of a given key from a carrier.
     */
    public function get($carrier, string $key) : ?string;
}
