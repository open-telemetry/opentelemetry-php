<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Retry;

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
 *
 *    $retryPolicy = new ExponentialWithJitterRetryPolicy(
 *          $maxAttempts = ExponentialWithJitterRetryPolicy::DEFAULT_MAX_ATTEMPTS,
 *          $initialBackoff = ExponentialWithJitterRetryPolicy::DEFAULT_INITIAL_BACKOFF,
 *          $maxBackoff = ExponentialWithJitterRetryPolicy::DEFAULT_MAX_BACKOFF,
 *          $backoffMultiplier = ExponentialWithJitterRetryPolicy::DEFAULT_BACKOFF_MULTIPLIER,
 *          $jitter = ExponentialWithJitterRetryPolicy::DEFAULT_JITTER,
 *          $retryableStatusCodes = [
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
 *      );
 *    $this->setRetryPolicy($retryPolicy);
 *    $this->setDelayScheduler(new BlockingScheduler());
 *
 *    The arguments in ExponentialWithJitterRetryPolicy() are as follows:
 *          maxAttempts,        - maximum no of retry attempts allowed
 *          initialBackoff,     - no of secs to wait before retrying for the first time
 *          maxBackoff,         - maximum no of sec to wait before retry timeout
 *          backoffMultiplier,  - multiplier value used to calculate the delay before next attempt
 *          jitter,             - jitter value to add randomness to delay value
 *          retryableStatusCodes- array of retryable status code. All the status codes apart from this will not be retried
 *      )
 *
 *    DelayScheduler is used to define the delay method for the exporter whether should it block the current execution
 *    or not.
 *    NonBlockingScheduler will be more useful when fiber is implemented.
 *    By Default scheduler is of type BlockingScheduler.
 */
interface RetryPolicyInterface
{
    public function shouldRetry(
        int $attempt,
        int $status
    ): ?bool;

    public function getDelay(int $attempt): int;

}
