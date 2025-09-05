<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#getter-argument
 */
interface PropagationGetterInterface
{
    /**
     * Returns the list of all the keys in the carrier.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#keys
     *
     * @return list<string>
     */
    public function keys(mixed $carrier): array;

    /**
     * Gets the value of a given key from a carrier.
     */
    public function get(mixed $carrier, string $key) : ?string;
}
