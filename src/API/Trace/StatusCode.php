<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#set-status
 */
final class StatusCode
{
    public const STATUS_UNSET = 'Unset';
    public const STATUS_OK = 'Ok';
    public const STATUS_ERROR = 'Error';

    public function getChoices(): array
    {
        return [
            self::STATUS_UNSET,
            self::STATUS_OK,
            self::STATUS_ERROR,
        ];
    }

    private function __construct()
    {
    }
}
