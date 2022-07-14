<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Retry;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Time\BlockingScheduler;
use OpenTelemetry\SDK\Common\Time\SchedulerInterface;
use OpenTelemetry\SDK\Metrics\Exceptions\RetryableExportException;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

final class ExponentialWithJitterRetryPolicy implements RetryPolicyInterface
{
    private $maxAttempts;
    private $initialBackoff;
    private $maxBackoff;
    private $backoffMultiplier;
    private $jitter;
    public $retryableStatusCodes;
    public $scheduler;

    public function __construct($arguments=[])
    {
        extract($arguments, EXTR_IF_EXISTS);
        $this->setMaxAttempts($arguments['maxAttempts'] ?? self::DEFAULT_MAX_ATTEMPTS);
        $this->setInitialBackoff($arguments['initialBackoff'] ?? self::DEFAULT_INITIAL_BACKOFF);
        $this->setMaxBackoff($arguments['maxBackoff'] ?? self::DEFAULT_MAX_BACKOFF);
        $this->setBackoffMultipler($arguments['backoffMultiplier'] ?? self::DEFAULT_BACKOFF_MULTIPLIER);
        $this->setJitter($arguments['jitter'] ?? self::DEFAULT_JITTER);
        $this->setRetryableStatusCodes($arguments['retryableStatusCodes'] ?? []);
        $this->setDelayScheduler($arguments['scheduler'] ?? new BlockingScheduler());
    }

    public function shouldRetry(
        int $attempt,
        int $status,
        ?RetryableExportException $exception = null
    ): bool {
        if ($attempt >= $this->maxAttempts && null !== $exception) {
            throw $exception;
        }

        return $attempt < $this->maxAttempts &&
            $status == SpanExporterInterface::STATUS_FAILED_RETRYABLE;
    }

    public function getDelay(int $attempt): int
    {
        // Dont wait for the first attempt
        if ($attempt == 0) {
            return 0;
        }
        // Initial exponential backoff in mili seconds
        $delay = ($this->backoffMultiplier ** $attempt) * $this->initialBackoff * 1000;

        // Adding jitter to exponential backoff
        if ($this->jitter > 0) {
            $randomness = (int) ($delay * $this->jitter);
            $delay += rand(-$randomness, +$randomness);
        }

        return min((int) $delay, $this->maxBackoff * 1000);
    }

    public function delay(int $timeout): void
    {
        $this->scheduler->delay($timeout);
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function setMaxAttempts(
        int $max_attempts = self::DEFAULT_MAX_ATTEMPTS
    ) {
        $this->checkArgument(
            $max_attempts > 0,
            sprintf('Max attempts value must be > 0 and of int type. "%s" is given.', $max_attempts)
        );
        $this->maxAttempts = $max_attempts;

        return $this;
    }

    public function getInitialBackoff(): float
    {
        return $this->initialBackoff;
    }

    public function setInitialBackoff(
        float $initial_backoff = self::DEFAULT_INITIAL_BACKOFF
    ) {
        $this->checkArgument(
            $initial_backoff > 0,
            sprintf('Initial backoff must be greater than 0: "%s" value provided', $initial_backoff)
        );
        $this->initialBackoff = $initial_backoff;

        return $this;
    }

    public function getMaxBackoff(): int
    {
        return $this->maxBackoff;
    }

    public function setMaxBackoff(int $max_backoff = self::DEFAULT_MAX_BACKOFF)
    {
        $this->checkArgument(
            $max_backoff > 0,
            sprintf('Max backoff must be greater than 0: "%s" value provided', $max_backoff)
        );
        $this->maxBackoff = $max_backoff;

        return $this;
    }

    public function getBackoffMultiplier(): float
    {
        return $this->backoffMultiplier;
    }

    public function setBackoffMultipler(
        float $backoff_multiplier = self::DEFAULT_BACKOFF_MULTIPLIER
    ) {
        $this->checkArgument(
            $backoff_multiplier > 1,
            sprintf('Backoff multiplier must be greater than 0: "%s" value provided', $backoff_multiplier)
        );
        $this->backoffMultiplier = $backoff_multiplier;

        return $this;
    }

    public function getJitter(): float
    {
        return $this->jitter;
    }

    public function setJitter(float $jitter = self::DEFAULT_JITTER)
    {
        $this->checkArgument(
            $jitter >= 0 && $jitter <= 1,
            sprintf('Jitter value must be between 0 and 1: "%s" value provided', $jitter)
        );
        $this->jitter = $jitter;

        return $this;
    }

    public function setRetryableStatusCodes(array $statusCodes)
    {
        $this->checkArgument(
            $statusCodes !== [] &&
            array_reduce(
                $statusCodes,
                fn ($result, $item) => $result && is_int($item),
                true
            ),
            'Retryable Status Code array should not be empty 
                    and each value should be valid status code'
        );
        $this->retryableStatusCodes = $statusCodes;

        return $this;
    }

    public function getRetryableStatusCodes(): array
    {
        return $this->retryableStatusCodes;
    }

    public function setDelayScheduler(SchedulerInterface $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    public function getDelayScheduler(): SchedulerInterface
    {
        return $this->scheduler;
    }

    public function checkArgument(bool $argument_condition, string $message)
    {
        if (!$argument_condition) {
            throw new InvalidArgumentException($message);
        }
    }
}
