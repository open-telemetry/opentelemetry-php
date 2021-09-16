<?php

declare(strict_types=1);

namespace OpenTelemetry\Baggage;

use OpenTelemetry\Baggage as API;

interface BaggageBuilder
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#set-value
     */
    public function set(string $key, $value, ?API\Metadata $metadata = null): API\BaggageBuilder;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#remove-value
     */
    public function remove(string $key): API\BaggageBuilder;

    public function build(): API\Baggage;
}
