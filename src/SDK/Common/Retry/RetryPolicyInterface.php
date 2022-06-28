<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Retry;

use OpenTelemetry\SDK\Metrics\Exceptions\RetryableExportException;

/**
 * This interface is to implement retry policy for exporters where in case of failure send the spans,
 * based on few parameters, exporter will wait for specific period and retry sending them for upto maxAttempts.
 * As per the specification (https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/exporter.md#retry),
 * it is required to implement the exponential retrypolicy with jitter.
 *
 * By default there is no retry policy set.
 *
 * Here is the example how exporter can use this policy
 * i.e. lets say if user wants to enable retry policy for grpc exporter
 * Add the following code in the exporter;
 *    Use SpanExporterTrait;
 *
 *    The following line will enable the exponential retry policy with jitter with
 *    default values.
 *    $this->setRetryPolicy(ExponentialWithJitterRetryPolicy::getDefault());
 *
 *    User can provide different values for retry params as well as follows:
 *    $this->setRetryPolicy(
 *          new ExponentialWithJitterRetryPolicy(
 *              5, 1, 10, 1.5, 0.1,
 *              [\Grpc\STATUS_CANCELLED,
 *              \Grpc\STATUS_DEADLINE_EXCEEDED,
 *              \Grpc\STATUS_PERMISSION_DENIED,
 *              \Grpc\STATUS_RESOURCE_EXHAUSTED,
 *              \Grpc\STATUS_ABORTED,
 *              \Grpc\STATUS_OUT_OF_RANGE,
 *              \Grpc\STATUS_UNAVAILABLE,
 *              \Grpc\STATUS_DATA_LOSS,
 *              \Grpc\STATUS_UNAUTHENTICATED,
 *          ]));
 *
 *    The arguments in ExponentialWithJitterRetryPolicy() are as follows:
 *      ExponentialWithJitterRetryPolicy(
 *          defaultMaxAttempts, - total no of retry attempts allowed
 *          initialBackoff,     - total no of secs to wait before retrying for the first time
 *          maxBackoff,         - maximum no of sec to wait before retry timeout
 *          backoffMultiplier,  - multiplier value used to calculate the delay before next attempt
 *          jitter,             - jitter value to add randomness to delay value
 *          retryableStatusCodes- array of retryable status code. All the status codes apart from this will not be retried
 *      )
 *
 *    User can set the retryable status codes separately as well using following api
 *    $this->setRetryableStatusCodes([
 *      \Grpc\STATUS_CANCELLED,
 *      \Grpc\STATUS_DEADLINE_EXCEEDED,
 *      \Grpc\STATUS_PERMISSION_DENIED,
 *      \Grpc\STATUS_RESOURCE_EXHAUSTED,
 *      \Grpc\STATUS_ABORTED,
 *      \Grpc\STATUS_OUT_OF_RANGE,
 *      \Grpc\STATUS_UNAVAILABLE,
 *      \Grpc\STATUS_DATA_LOSS,
 *      \Grpc\STATUS_UNAUTHENTICATED,
 *      ]);
 *
 */
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
     * @param RetryableExportException|null $exception: exception caught while retrying
     *
     * @return bool
     */
    public function shouldRetry(
        int $attempt,
        int $status,
        ?RetryableExportException $exception
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

    public function getRetryableStatusCodes(): array;
}
