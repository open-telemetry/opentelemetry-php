<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs\Map;

use InvalidArgumentException;
use OpenTelemetry\API\Logs\Severity;
use Psr\Log\LogLevel;

class Psr3
{
    /**
     * Maps PSR-3 severity level (string) to the appropriate opentelemetry severity
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/data-model-appendix.md#appendix-b-severitynumber-example-mappings
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/data-model.md#field-severitynumber
     */
    public static function severityNumber(string $level): Severity
    {
        return match (strtolower($level)) {
            LogLevel::DEBUG => Severity::DEBUG,
            LogLevel::INFO => Severity::INFO,
            LogLevel::NOTICE => Severity::INFO2,
            LogLevel::WARNING => Severity::WARN,
            LogLevel::ERROR => Severity::ERROR,
            LogLevel::CRITICAL => Severity::ERROR2,
            LogLevel::ALERT => Severity::ERROR3,
            LogLevel::EMERGENCY => Severity::FATAL,
            default => throw new InvalidArgumentException('Unknown severity: ' . $level),
        };
    }
}
