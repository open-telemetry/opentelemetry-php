<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal;

use OpenTelemetry\API\Behavior\Internal\LogWriter\ErrorLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Behavior\Internal\LogWriter\NoopLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\Psr3LogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\StreamLogWriter;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\LoggerHolder;
use Psr\Log\LogLevel;

/**
 * Logging utility functions for internal logging (of OpenTelemetry errors/warnings etc).
 * This is not part of SDK configuration to avoid creating a dependency on SDK from any package which does logging.
 * @todo this should be `@internal`, but deptrac is not happy with that.
 */
class Logging
{
    private const OTEL_LOG_LEVEL = 'OTEL_LOG_LEVEL';
    private const OTEL_PHP_LOG_DESTINATION = 'OTEL_PHP_LOG_DESTINATION';
    private const DEFAULT_LEVEL = LogLevel::INFO;
    private const DEFAULT = 'default';
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

    public static function logWriter(): LogWriterInterface
    {
        self::$writer ??= self::getLogWriter();

        return self::$writer;
    }

    private static function getLogWriter(): LogWriterInterface
    {
        $dest = Globals::configurationResolver()->getString(self::OTEL_PHP_LOG_DESTINATION);
        //we might not have an SDK, so attempt to get from environment
        if (!$dest) {
            $dest = array_key_exists(self::OTEL_PHP_LOG_DESTINATION, $_SERVER)
                ? $_SERVER[self::OTEL_PHP_LOG_DESTINATION]
                : getenv(self::OTEL_PHP_LOG_DESTINATION);
        }
        if (!$dest) {
            $dest = ini_get(self::OTEL_PHP_LOG_DESTINATION);
        }
        if (!$dest) {
            $dest = self::DEFAULT;
        }
        $logger = LoggerHolder::get();

        switch ($dest) {
            case 'none':
                return new NoopLogWriter();
            case 'stderr':
                return new StreamLogWriter('php://stderr');
            case 'stdout':
                return new StreamLogWriter('php://stdout');
            case 'psr3':
                if ($logger) {
                    return new Psr3LogWriter($logger);
                }
                error_log('OpenTelemetry: cannot use OTEL_PHP_LOG_DESTINATION=psr3 without providing a PSR-3 logger');
                //default to error log
                return new ErrorLogWriter();
            case 'error_log':
                return new ErrorLogWriter();
            default:
                if ($logger) {
                    return new Psr3LogWriter($logger);
                }

                return new ErrorLogWriter();
        }
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
}
