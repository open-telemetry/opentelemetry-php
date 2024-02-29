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
        return match (strtolower($level)) {
            LogLevel::DEBUG => 5,
            LogLevel::INFO => 9,
            LogLevel::NOTICE => 10,
            LogLevel::WARNING => 13,
            LogLevel::ERROR => 17,
            LogLevel::CRITICAL => 18,
            LogLevel::ALERT => 19,
            LogLevel::EMERGENCY => 21,
            default => 0,
        };
    }
}
