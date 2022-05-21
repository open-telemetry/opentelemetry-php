<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Retry;

// use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse;
use OpenTelemetry\SDK\Metrics\Exceptions\RetryableExportException;

interface RetryPolicyInterface
{
    public const DEFAULT_MAX_ATTEMPTS = 5;
    public const DEFAULT_INITIAL_BACKOFF = 1;
    public const DEFAULT_MAX_BACKOFF = 10;
    public const DEFAULT_BACKOFF_MULTIPLIER = 1.5;
    public const DEFAULT_JITTER = 0.1;

    /**
     * Returns whether the request should be retried.
     *
     * @param int $attempt - current number of retry attempt
     * @param int $status - SpanExporterStatusInterface
     * @param RetryableExportException $exception: exception caught while retrying
     *
     * @return bool
     */
    public function shouldRetry(
        int $attempt,
        int $status,
        RetryableExportException $exception
    ): ?bool;

    /**
     * Returns the time to wait in seconds.
     * @param int $attempt: current number of retry attempt
     *
     * @return int
     */
    public function getDelay(int $attempt): int;

    public static function getDefault(): RetryPolicyInterface;

    public function getMaxAttempts(): int;

    public function setMaxAttempts(int $max_attempts);

    public function getInitialBackoff(): float;

    public function setInitialBackoff(float $initial_backoff);

    public function getMaxBackoff(): int;

    public function setMaxBackoff(int $max_backoff);

    public function getBackoffMultiplier(): float;

    public function setBackoffMultipler(float $backoff_multiplier);

    public function getJitter(): float;

    public function setJitter(float $jitter);

    public function setRetryableStatusCodes(array $statusCodes);

    public function getRetryableStatusCodes(): ?array;
}
