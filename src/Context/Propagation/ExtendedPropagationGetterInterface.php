<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

/**
 * Interface for getting values from a carrier.
 * This interface extends the base PropagationGetterInterface to avoid breaking changes.
 */
interface ExtendedPropagationGetterInterface extends PropagationGetterInterface
{
    /**
     * Gets all values of a given key from a carrier.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.44.0/specification/context/api-propagators.md#getall
     *
     * @return list<string>
     */
    public function getAll($carrier, string $key): array;
}
