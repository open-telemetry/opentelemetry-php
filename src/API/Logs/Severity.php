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

    /**
     * Resolves a case-insensitive case name, as used by the `SeverityNumber` config enum.
     */
    public static function fromName(string $name): self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->name, $name) === 0) {
                return $case;
            }
        }

        throw new ValueError('Unknown severity: ' . $name);
    }

    /**
     * Maps to a PSR-3 level, inverting {@see self::fromPsr3()}.
     *
     * PSR-3's levels are the RFC 5424 severities, which the spec's example mappings pin to
     * particular cases: Notice to INFO2, Critical to ERROR2, Alert to ERROR3, Emergency to FATAL.
     * Nothing is pinned to the remaining numbered cases, so each maps to the nearest pinned case
     * at or *below* it. Mapping down rather than up keeps the result monotonic, which matters
     * where the level is a threshold: INFO3 outranks INFO2, so it must not map to a more verbose
     * PSR-3 level than INFO2 does. The trade is that a threshold may admit slightly more than
     * asked for, never less.
     *
     * @see https://opentelemetry.io/docs/specs/otel/logs/data-model-appendix/#appendix-b-severitynumber-example-mappings
     *
     * @return 'debug'|'info'|'notice'|'warning'|'error'|'critical'|'alert'|'emergency'
     */
    public function toPsr3(): string
    {
        return match ($this) {
            Severity::TRACE, Severity::TRACE2, Severity::TRACE3, Severity::TRACE4,
            Severity::DEBUG, Severity::DEBUG2, Severity::DEBUG3, Severity::DEBUG4 => LogLevel::DEBUG,
            Severity::INFO => LogLevel::INFO,
            Severity::INFO2, Severity::INFO3, Severity::INFO4 => LogLevel::NOTICE,
            Severity::WARN, Severity::WARN2, Severity::WARN3, Severity::WARN4 => LogLevel::WARNING,
            Severity::ERROR => LogLevel::ERROR,
            Severity::ERROR2 => LogLevel::CRITICAL,
            Severity::ERROR3, Severity::ERROR4 => LogLevel::ALERT,
            Severity::FATAL, Severity::FATAL2, Severity::FATAL3, Severity::FATAL4 => LogLevel::EMERGENCY,
        };
    }
}
