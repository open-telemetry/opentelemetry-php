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
 *
 * Add the following code in the exporter;
 *    Use SpanExporterTrait;
 *
 *    The following line will enable the exponential retry policy with jitter with
 *    default values.
 *    Only mandatory argument is "retryableStatusCodes" as they are different for different types of exporters
 *
 *    $this->setRetryPolicy(new ExponentialWithJitterRetryPolicy(["retryableStatusCodes" => \GRPC\STATUS_CANCELLED]));
 *
 *    User can provide different values for retry params as well as follows:
 *    $retryPolicy = new ExponentialWithJitterRetryPolicy([
 *          "maxAttempt" => 5,
 *          "initialBackoff" => 1,
 *          "maxBackoff" => 10,
 *          "backoffMultiplier" => 1.5,
 *          "jitter" => 0.1,
 *          "scheduler" => new BlockingScheduler(),
 *          "retryableStatusCodes" => [
 *              \GRPC\STATUS_CANCELLED,
 *              \Grpc\STATUS_DEADLINE_EXCEEDED,
 *              \Grpc\STATUS_PERMISSION_DENIED,
 *              \Grpc\STATUS_RESOURCE_EXHAUSTED,
 *              \Grpc\STATUS_ABORTED,
 *              \Grpc\STATUS_OUT_OF_RANGE,
 *              \Grpc\STATUS_UNAVAILABLE,
 *              \Grpc\STATUS_DATA_LOSS,
 *              \Grpc\STATUS_UNAUTHENTICATED,
 *          ],
 *      ]);
 *    $this->setRetryPolicy($retryPolicy);
 *
 *    The arguments in ExponentialWithJitterRetryPolicy() are as follows:
 *          maxAttempts,        - maximum no of retry attempts allowed
 *          initialBackoff,     - no of secs to wait before retrying for the first time
 *          maxBackoff,         - maximum no of sec to wait before retry timeout
 *          backoffMultiplier,  - multiplier value used to calculate the delay before next attempt
 *          jitter,             - jitter value to add randomness to delay value
 *          scheduler           - Blocking or Non Blocking delay scheduler (default: BlockingSchuduler)
 *          retryableStatusCodes- array of retryable status code. All the status codes apart from this will not be retried
 *      )
 *
 *    NonBlockingScheduler will be more useful when fiber is implemented.
 *    By Default scheduler is of type BlockingScheduler.
 */
interface RetryPolicyInterface
{
    public const DEFAULT_MAX_ATTEMPTS = 5;
    public const DEFAULT_INITIAL_BACKOFF = 1;
    public const DEFAULT_MAX_BACKOFF = 10;
    public const DEFAULT_BACKOFF_MULTIPLIER = 1.5;
    public const DEFAULT_JITTER = 0.1;

    public function shouldRetry(
        int $attempt,
        int $status,
        ?RetryableExportException $exception
    ): ?bool;

    public function getDelay(int $attempt): int;

    /**
     * delay the execution of the thread by $timeout milliseconds
     * @param  int $timeout: milliseconds to delay the execution
     */
    public function delay(int $timeout): void;
}
