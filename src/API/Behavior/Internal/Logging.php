<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal;

use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Behavior\Internal\LogWriter\NoopLogWriter;
use Psr\Log\LogLevel;

/**
 * Logging utility functions for internal logging (of OpenTelemetry errors/warnings etc).
 * This is not part of SDK configuration to avoid creating a dependency on SDK from any package which does logging.
 * @todo this should be `@internal`, but deptrac is not happy with that.
 */
class Logging
{
    private const OTEL_LOG_LEVEL = 'OTEL_LOG_LEVEL';
    private const DEFAULT_LEVEL = LogLevel::INFO;
    private const NONE = 'none';
    private const LEVELS = [
        LogLevel::DEBUG,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
        LogLevel::ERROR,
        LogLevel::CRITICAL,
        LogLevel::ALERT,
        LogLevel::EMERGENCY,
        self::NONE, //highest priority so that nothing is logged
    ];

    /**
     * The minimum log level. Messages with lower severity than this will be ignored.
     */
    private static ?int $logLevel = null;
    private static ?LogWriterInterface $writer = null;

    public static function setLogWriter(LogWriterInterface $writer): void
    {
        self::$writer = $writer;
    }

    public static function logWriter(): LogWriterInterface
    {
        self::$writer ??= (new LogWriterFactory())->create();

        return self::$writer;
    }

    /**
     * Get level priority from a PSR-3 level name, or 'none'. An unrecognised name yields the
     * priority of 'info' rather than an error.
     */
    public static function level(string $level): int
    {
        $value = array_search($level, self::LEVELS);

        return $value !== false ? $value : 1; //'info'
    }

    /**
     * Set the minimum log level, taking precedence over OTEL_LOG_LEVEL.
     *
     * Takes a PSR-3 level name ({@see LogLevel}) or 'none' to log nothing. OpenTelemetry severity
     * names are not accepted: an unrecognised name is not an error, it silently means 'info'
     * ({@see self::level()}), so callers holding an OTel severity must map it first
     * ({@see \OpenTelemetry\API\Logs\Severity::toPsr3()}).
     *
     * @param 'debug'|'info'|'notice'|'warning'|'error'|'critical'|'alert'|'emergency'|'none' $level
     */
    public static function setLogLevel(string $level): void
    {
        self::$logLevel = self::level($level);
    }

    /**
     * Get defined OTEL_LOG_LEVEL, or default
     */
    public static function logLevel(): int
    {
        self::$logLevel ??= self::getLogLevel();

        return self::$logLevel;
    }

    private static function getLogLevel(): int
    {
        $level = array_key_exists(self::OTEL_LOG_LEVEL, $_SERVER)
            ? $_SERVER[self::OTEL_LOG_LEVEL]
            : getenv(self::OTEL_LOG_LEVEL);
        if (!$level) {
            $level = ini_get(self::OTEL_LOG_LEVEL);
        }
        if (!$level) {
            $level = self::DEFAULT_LEVEL;
        }

        return self::level($level);
    }

    public static function reset(): void
    {
        self::$logLevel = null;
        self::$writer = null;
    }

    public static function disable(): void
    {
        self::$writer = new NoopLogWriter();
    }
}
