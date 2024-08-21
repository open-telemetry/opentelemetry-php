<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

use OpenTelemetry\API\Baggage as API;

interface BaggageBuilderInterface
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#set-value
     */
    public function set(string $key, mixed $value, ?API\MetadataInterface $metadata = null): API\BaggageBuilderInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#remove-value
     */
    public function remove(string $key): API\BaggageBuilderInterface;

    public function build(): API\BaggageInterface;
}
