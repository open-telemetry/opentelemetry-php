<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior;

use OpenTelemetry\API\Common\Log\LoggerHolder;
use Psr\Log\LogLevel;

trait LogsMessagesTrait
{
    private static function shouldLog(string $level): bool
    {
        return in_array($level, [LogLevel::ERROR, LogLevel::WARNING, LogLevel::CRITICAL, LogLevel::EMERGENCY]);
    }

    private static function map(string $level): int
    {
        switch ($level) {
            case LogLevel::WARNING:
                return E_USER_WARNING;
            case LogLevel::ERROR:
            case LogLevel::CRITICAL:
            case LogLevel::EMERGENCY:
                return E_USER_ERROR;
            default:
                return E_USER_NOTICE;
        }
    }

    private static function doLog(string $level, string $message, array $context): void
    {
        $logger = LoggerHolder::get();
        if ($logger !== null) {
            $context['source'] = get_called_class();
            $logger->log($level, $message, $context);
        } elseif (self::shouldLog($level)) {
            $message = sprintf(
                '%s: %s in %s',
                $message,
                (array_key_exists('exception', $context) && $context['exception'] instanceof \Throwable) ? $context['exception']->getMessage() : '',
                get_called_class()
            );
            error_log($message, self::map($level));
        }
    }

    protected static function logDebug(string $message, array $context = []): void
    {
        self::doLog(LogLevel::DEBUG, $message, $context);
    }

    protected static function logInfo(string $message, array $context = []): void
    {
        self::doLog(LogLevel::INFO, $message, $context);
    }

    protected static function logNotice(string $message, array $context = []): void
    {
        self::doLog(LogLevel::NOTICE, $message, $context);
    }

    protected static function logWarning(string $message, array $context = []): void
    {
        self::doLog(LogLevel::WARNING, $message, $context);
    }

    protected static function logError(string $message, array $context = []): void
    {
        self::doLog(LogLevel::ERROR, $message, $context);
    }
}
