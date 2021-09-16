<?php

declare(strict_types=1);

namespace OpenTelemetry\Baggage;

use OpenTelemetry\Baggage as API;
use OpenTelemetry\Context\ImplicitContextKeyed;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#operations
 */
interface Baggage extends ImplicitContextKeyed
{
    /**
     * @return mixed
     */
    public function getEntry(string $key): ?API\Entry;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#get-value
     */
    public function getValue(string $key);

    /**
     * @todo: What should this return? Array? Generator? iterable?
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#get-all-values
     */
    public function getAll();

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#set-value
     */
    public function set(string $key, $value, ?API\Metadata $metadata = null);

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#remove-value
     */
    public function remove(string $key): void;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#clear-baggage-in-the-context
     */
    public function clear(): void;
}
