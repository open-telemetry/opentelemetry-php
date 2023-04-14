<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use Psr\Log\LoggerAwareTrait as PsrTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

trait LoggerAwareTrait
{
    use PsrTrait;

    private string $defaultLogLevel = LogLevel::INFO;

    /**
     * @param string $logLevel
     */
    public function setDefaultLogLevel(string $logLevel): void
    {
        $this->defaultLogLevel = $logLevel;
    }

    /**
     * @param string $message
     * @param array $context
     * @param string|null $level
     */
    protected function log(string $message, array $context = [], ?string $level = null): void
    {
        $this->getLogger()->log(
            $level ?? $this->defaultLogLevel,
            $message,
            $context
        );
    }

    protected function getLogger(): LoggerInterface
    {
        if ($this->logger !== null) {
            return $this->logger;
        }

        return new NullLogger();
    }
}
