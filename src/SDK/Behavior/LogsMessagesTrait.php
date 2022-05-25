<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Behavior;

use OpenTelemetry\SDK\Common\Log\LoggerHolder;
use Psr\Log\LogLevel;

trait LogsMessagesTrait
{
    private static function writeLog(string $level, string $message, array $context): void
    {
        $context['source'] = get_called_class();
        LoggerHolder::get()->log($level, $message, $context);
    }

    protected static function logDebug(string $message, array $context = []): void
    {
        self::writeLog(LogLevel::DEBUG, $message, $context);
    }

    protected static function logInfo(string $message, array $context = []): void
    {
        self::writeLog(LogLevel::INFO, $message, $context);
    }

    protected static function logNotice(string $message, array $context = []): void
    {
        self::writeLog(LogLevel::NOTICE, $message, $context);
    }

    protected static function logWarning(string $message, array $context = []): void
    {
        self::writeLog(LogLevel::WARNING, $message, $context);
    }

    protected static function logError(string $message, array $context = []): void
    {
        self::writeLog(LogLevel::ERROR, $message, $context);
    }
}
