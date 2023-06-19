<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\LoggerHolder;
use Psr\Log\LogLevel;

trait LogsMessagesTrait
{
    private static function shouldLog(string $level): bool
    {
        return Logging::level($level) >= Logging::logLevel();
    }

    private static function doLog(string $level, string $message, array $context): void
    {
        $logger = LoggerHolder::get();
        if ($logger !== null) {
            $context['source'] = get_called_class();
            $logger->log($level, $message, $context);
        } elseif (self::shouldLog($level)) {
            $exception = (array_key_exists('exception', $context) && $context['exception'] instanceof \Throwable)
            ? $context['exception']
            : null;
            if ($exception) {
                $message = sprintf(
                    '%s: %s%s%s',
                    $message,
                    $exception->getMessage(),
                    PHP_EOL,
                    $exception->getTraceAsString()
                );
            } else {
                //get calling location, skipping over trait
                $caller = debug_backtrace()[1];
                $message = sprintf(
                    '%s(%s): %s',
                    $caller['file'],
                    $caller['line'],
                    $message,
                );
            }
            trigger_error($message, Logging::map($level));
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
