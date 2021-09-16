<?php

declare(strict_types=1);

namespace OpenTelemetry\Baggage;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#set-value
 */
interface Metadata
{
    public function getMetadata(): string;
}
