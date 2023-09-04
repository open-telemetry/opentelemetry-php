<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#set-status
 */
interface StatusCode
{
    public const STATUS_UNSET = 'Unset';
    public const STATUS_OK = 'Ok';
    public const STATUS_ERROR = 'Error';
}
