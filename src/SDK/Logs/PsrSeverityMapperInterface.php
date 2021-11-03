<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use Psr\Log\LogLevel as PsrLogLevel;

interface PsrSeverityMapperInterface
{
    /**
     * Severity code according to rfc5424 (Syslog Protocol)
     * @see : https://datatracker.ietf.org/doc/html/rfc5424#page-10
     */
    public const RFC_CODE = [
        // Detailed debug information.
        PsrLogLevel::DEBUG => 7,
        // Interesting events. Examples: User logs in, SQL logs.
        PsrLogLevel::INFO => 6,
        // Normal but significant events.
        PsrLogLevel::NOTICE => 5,
        // Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API,
        // undesirable things that are not necessarily wrong.
        PsrLogLevel::WARNING => 4,
        // Runtime errors that do not require immediate action but should typically be logged and monitored.
        PsrLogLevel::ERROR => 3,
        // Critical conditions. Example: Application component unavailable, unexpected exception.
        PsrLogLevel::CRITICAL => 2,
        // Action must be taken immediately. Example: Entire website down, database unavailable, etc.
        // This should trigger the alerts and wake you up.
        PsrLogLevel::ALERT => 1,
        // Emergency: system is unusable.
        PsrLogLevel::EMERGENCY => 0,
    ];

    /**
     * Mappig of OpenTelemetry SeverityNumber to PsrLogLevel.
     * @see: https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/logs/data-model.md#field-severitynumber
     */
    public const SEVERITY_NUMBER = [
        PsrLogLevel::DEBUG => 5,
        PsrLogLevel::INFO => 9,
        PsrLogLevel::NOTICE => 10,
        PsrLogLevel::WARNING => 13,
        PsrLogLevel::ERROR => 17,
        PsrLogLevel::CRITICAL => 18,
        PsrLogLevel::ALERT => 21,
        PsrLogLevel::EMERGENCY => 22,
    ];
}
