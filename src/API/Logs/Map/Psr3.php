<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs\Map;

use Psr\Log\LogLevel;

class Psr3
{
    /**
     * Maps PSR-3 severity level (string) to the appropriate opentelemetry severity number
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/data-model-appendix.md#appendix-b-severitynumber-example-mappings
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/data-model.md#field-severitynumber
     */
    public static function severityNumber(string $level): int
    {
        switch (strtolower($level)) {
            case LogLevel::DEBUG:
                return 5;
            case LogLevel::INFO:
                return 9;
            case LogLevel::NOTICE:
                return 10;
            case LogLevel::WARNING:
                return 13;
            case LogLevel::ERROR:
                return 17;
            case LogLevel::CRITICAL:
                return 18;
            case LogLevel::ALERT:
                return 19;
            case LogLevel::EMERGENCY:
                return 21;
            default:
                return 0;
        }
    }
}
