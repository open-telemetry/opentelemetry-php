<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait as PsrTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * TODO this trait is useful in other modules (eg Metrics) and should be pulled up to OpenTelemetry\SDK
 */
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
     * @return static
     * @suppress PhanParamSignatureMismatch
     * @suppress PhanTypeInvalidTraitReturn
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @param string|null $level
     */
    protected function log(string $message, array $context = [], ?string $level = null): void
    {
        $context['source'] = get_class($this);
        $this->getLogger()->log(
            $level ?? $this->defaultLogLevel,
            $message,
            $context
        );
    }

    protected function logDebug(string $message, array $context = []): void
    {
        $this->log($message, $context, LogLevel::DEBUG);
    }

    protected function logWarning(string $message, array $context = []): void
    {
        $this->log($message, $context, LogLevel::WARNING);
    }

    protected function logError(string $message, array $context = []): void
    {
        $this->log($message, $context, LogLevel::ERROR);
    }

    protected function logInfo(string $message, array $context = []): void
    {
        $this->log($message, $context, LogLevel::INFO);
    }

    protected function logNotice(string $message, array $context = []): void
    {
        $this->log($message, $context, LogLevel::NOTICE);
    }

    /**
     * Inject the logger into another class that implements LoggerAwareInterface, and
     * return the instance.
     */
    protected function injectLogger(object $instance): object
    {
        if ($instance instanceof LoggerAwareInterface) {
            $instance->setLogger($this->getLogger());
        }

        return $instance;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger instanceof LoggerInterface ? $this->logger : $this->logger = new NullLogger();
    }
}
