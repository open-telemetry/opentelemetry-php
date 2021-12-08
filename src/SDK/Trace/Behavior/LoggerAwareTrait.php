<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait as PsrTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

trait LoggerAwareTrait
{
    use PsrTrait;

    private string $defaultLogLevel = LogLevel::INFO;

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param string $logLevel
     */
    public function setDefaultLogLevel(string $logLevel): self
    {
        $this->defaultLogLevel = $logLevel;
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @param string|null $level
     */
    protected function log(string $message, array $context = [], ?string $level = null): void
    {
        $context['caller'] = __CLASS__;
        $this->getLogger()->log(
            $level ?? $this->defaultLogLevel,
            $message,
            $context
        );
    }

    protected function injectLogger($class): void
    {
        if ($class instanceof LoggerAwareInterface) {
            $class->setLogger($this->getLogger());
        }
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger instanceof LoggerInterface ? $this->logger : $this->logger = new NullLogger();
    }
}
