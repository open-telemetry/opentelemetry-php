<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal;

use Psr\Log\LogLevel;

/**
 * Logging utility functions for default (error_log) logging.
 * This is not part of SDK configuration to avoid creating a dependency on SDK from any package which does logging.
 * @todo this should be `@internal`, but deptrac is not happy with that.
 */
class Logging
{
    private const VARIABLE_NAME = 'OTEL_LOG_LEVEL';
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

    /**
     * Get level priority from level name
     */
    public static function level(string $level): int
    {
        $value = array_search($level, self::LEVELS);

        return $value ?: 1; //'info'
    }

    /**
     * Get defined OTEL_LOG_LEVEL, or default
     */
    public static function logLevel(): int
    {
        self::$logLevel ??= self::getLogLevel();

        return self::$logLevel;
    }

    /**
     * Map PSR-3 levels to error_log levels.
     * Note that we should never use higher than E_USER_WARNING so that we do not break user applications.
     */
    public static function map(string $level)
    {
        switch ($level) {
            case LogLevel::WARNING:
            case LogLevel::ERROR:
            case LogLevel::CRITICAL:
            case LogLevel::EMERGENCY:
                return E_USER_WARNING;
            default:
                return E_USER_NOTICE;
        }
    }

    private static function getLogLevel(): int
    {
        $level = array_key_exists(self::VARIABLE_NAME, $_SERVER)
            ? $_SERVER[self::VARIABLE_NAME]
            : getenv(self::VARIABLE_NAME);
        if (!$level) {
            $level = ini_get(self::VARIABLE_NAME);
        }
        if (!$level) {
            $level = self::DEFAULT_LEVEL;
        }

        return self::level($level);
    }

    public static function reset(): void
    {
        self::$logLevel = null;
    }
}
