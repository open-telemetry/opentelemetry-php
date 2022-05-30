<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Retry;

use InvalidArgumentException;
use OpenTelemetry\SDK\Metrics\Exceptions\RetryableExportException;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class ExponentialWithJitterRetryPolicy implements RetryPolicyInterface
{
    private $maxAttempts;
    private $initial_backoff;
    private $maxBackoff;
    private $backoff_multiplier;
    private $jitter;
    public $retryableStatusCodes;

    private function __construct(
        int $max_attempts = self::DEFAULT_MAX_ATTEMPTS,
        float $initial_backoff = self::DEFAULT_INITIAL_BACKOFF,
        int $max_backoff = self::DEFAULT_MAX_BACKOFF,
        float $backoff_multiplier = self::DEFAULT_BACKOFF_MULTIPLIER,
        float $jitter = self::DEFAULT_JITTER,
        array $retryableStatusCodes = null
    ) {
        $this->setMaxAttempts($max_attempts);
        $this->setInitialBackoff($initial_backoff);
        $this->setMaxBackoff($max_backoff);
        $this->setBackoffMultipler($backoff_multiplier);
        $this->setJitter($jitter);
        $this->retryableStatusCodes = $retryableStatusCodes;
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
        // Initial exponential backoff in milli seconds
        $delay = ($this->backoff_multiplier ** $attempt) * $this->initial_backoff * 1000;
        // Adding jitter to exponential backoff
        if ($this->jitter > 0) {
            $randomness = (int) ($delay * $this->jitter);
            $delay = $delay + random_int(-$randomness, +$randomness);
        }

        // return $delay < $this->maxBackoff * 1000 ? (int) $delay : $this->maxBackoff;
        return min((int) $delay, $this->maxBackoff * 1000);
    }

    public static function getDefault(): ExponentialWithJitterRetryPolicy
    {
        return new ExponentialWithJitterRetryPolicy();
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
        return $this->initial_backoff;
    }

    public function setInitialBackoff(
        float $initial_backoff = self::DEFAULT_INITIAL_BACKOFF
    ) {
        $this->checkArgument(
            $initial_backoff > 0,
            sprintf('Initial backoff must be greater than 0: "%s" value provided', $initial_backoff)
        );
        $this->initial_backoff = $initial_backoff;

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
        return $this->backoff_multiplier;
    }

    public function setBackoffMultipler(
        float $backoff_multiplier = self::DEFAULT_BACKOFF_MULTIPLIER
    ) {
        $this->checkArgument(
            $backoff_multiplier > 0,
            sprintf('Backoff multiplier must be greater than 0: "%s" value provided', $backoff_multiplier)
        );
        $this->backoff_multiplier = $backoff_multiplier;

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
            count($statusCodes) > 0 &&
            array_reduce(
                $statusCodes,
                function ($result, $item) {
                    return $result && is_int($item);
                },
                true
            ),
            sprintf('Retryable Status Code array should not be empty 
                    and each value should be valid status code')
        );
        $this->retryableStatusCodes = $statusCodes;

        return $this;
    }

    public function getRetryableStatusCodes(): array
    {
        return $this->retryableStatusCodes;
    }

    public function checkArgument(bool $argument_condition, string $message)
    {
        if (!$argument_condition) {
            throw new InvalidArgumentException($message);
        }
    }
}
