<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#set-value
 */
interface MetadataInterface
{
    public function getValue(): string;
}
