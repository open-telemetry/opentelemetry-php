<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use Psr\Log\LogLevel;
use ValueError;

enum Severity: int
{
    case TRACE = 1;
    case TRACE2 = 2;
    case TRACE3 = 3;
    case TRACE4 = 4;
    case DEBUG = 5;
    case DEBUG2 = 6;
    case DEBUG3 = 7;
    case DEBUG4 = 8;
    case INFO = 9;
    case INFO2 = 10;
    case INFO3 = 11;
    case INFO4 = 12;
    case WARN = 13;
    case WARN2 = 14;
    case WARN3 = 15;
    case WARN4 = 16;
    case ERROR = 17;
    case ERROR2 = 18;
    case ERROR3 = 19;
    case ERROR4 = 20;
    case FATAL = 21;
    case FATAL2 = 22;
    case FATAL3 = 23;
    case FATAL4 = 24;

    /**
     * Maps PSR-3 severity level (string) to the appropriate opentelemetry severity
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/data-model-appendix.md#appendix-b-severitynumber-example-mappings
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/data-model.md#field-severitynumber
     */
    public static function fromPsr3(string $level): self
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
            default => throw new ValueError('Unknown severity: ' . $level),
        };
    }
}
