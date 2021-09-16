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
    public function getEntry(string $key): ?API\Entry;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#get-value
     *
     * @return mixed
     */
    public function getValue(string $key);

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#get-all-values
     */
    public function getAll(): iterable;

    public function toBuilder(): API\BaggageBuilder;
}
