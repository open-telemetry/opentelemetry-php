<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Behavior;

use OpenTelemetry\SDK\GlobalLoggerHolder;
use Psr\Log\LogLevel;

trait LogsMessagesTrait
{
    private function doLog(string $level, string $message, array $context): void
    {
        $context['source'] = get_class($this);
        GlobalLoggerHolder::get()->log($level, $message, $context);
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
