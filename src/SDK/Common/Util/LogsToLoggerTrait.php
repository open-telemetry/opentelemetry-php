<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

trait LogsToLoggerTrait
{
    abstract private function logger(): LoggerInterface;

    /**
     * Logging is best effort: a failing logger must not break the code that is being observed.
     */
    private function doLog(string $level, string $message, array $context): void
    {
        try {
            $context['source'] = get_called_class();
            $this->logger()->log($level, $message, $context);
        } catch (\Throwable) {
            // nothing more can be done
        }
    }

    protected function logDebug(string $message, array $context = []): void
    {
        $this->doLog(LogLevel::DEBUG, $message, $context);
    }

    protected function logInfo(string $message, array $context = []): void
    {
        $this->doLog(LogLevel::INFO, $message, $context);
    }

    protected function logNotice(string $message, array $context = []): void
    {
        $this->doLog(LogLevel::NOTICE, $message, $context);
    }

    protected function logWarning(string $message, array $context = []): void
    {
        $this->doLog(LogLevel::WARNING, $message, $context);
    }

    protected function logError(string $message, array $context = []): void
    {
        $this->doLog(LogLevel::ERROR, $message, $context);
    }
}
