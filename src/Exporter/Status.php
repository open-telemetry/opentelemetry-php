<?php declare(strict_types=1);

namespace OpenTelemetry\Exporter;

final class Status
{
    /**
     * Possible return values as outlined in the OpenTelemetry spec
     */
    const SUCCESS = 0;
    const FAILED_NOT_RETRYABLE = 1;
    const FAILED_RETRYABLE = 2;
}
