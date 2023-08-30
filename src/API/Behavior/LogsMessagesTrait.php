<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior;

use OpenTelemetry\API\Behavior\Internal\Logging;
use Psr\Log\LogLevel;

trait LogsMessagesTrait
{
    private static function shouldLog(string $level): bool
    {
        return Logging::level($level) >= Logging::logLevel();
    }

    private static function doLog(string $level, string $message, array $context): void
    {
        $writer = Logging::logWriter();
        if (self::shouldLog($level)) {
            $context['source'] = get_called_class();
            $writer->write($level, $message, $context);
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
