<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Retry;

use OpenTelemetry\SDK\Common\Retry\ExponentialWithJitterRetryPolicy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Retry\ExponentialWithJitterRetryPolicy
 */
class ExponentialWithJitterRetryPolicyTest extends TestCase
{
    public function test_retry_policy_set_properly(): void
    {
        $retryPolicy = new ExponentialWithJitterRetryPolicy(
            $maxAttempts = ExponentialWithJitterRetryPolicy::DEFAULT_MAX_ATTEMPTS,
            $initialBackoff = ExponentialWithJitterRetryPolicy::DEFAULT_INITIAL_BACKOFF,
            $maxBackoff = ExponentialWithJitterRetryPolicy::DEFAULT_MAX_BACKOFF,
            $backoffMultiplier = ExponentialWithJitterRetryPolicy::DEFAULT_BACKOFF_MULTIPLIER,
            $jitter = ExponentialWithJitterRetryPolicy::DEFAULT_JITTER,
            $retryableStatusCodes = [
                \Grpc\STATUS_CANCELLED,
                \Grpc\STATUS_DEADLINE_EXCEEDED,
                \Grpc\STATUS_RESOURCE_EXHAUSTED,
                \Grpc\STATUS_ABORTED,
                \Grpc\STATUS_OUT_OF_RANGE,
                \Grpc\STATUS_UNAVAILABLE,
                \Grpc\STATUS_DATA_LOSS,
            ],
        );
        $this->assertSame(
            $retryPolicy->getMaxAttempts(),
            ExponentialWithJitterRetryPolicy::DEFAULT_MAX_ATTEMPTS
        );
        $this->assertSame(
            (int) ($retryPolicy->getInitialBackoff()),
            ExponentialWithJitterRetryPolicy::DEFAULT_INITIAL_BACKOFF
        );
        $this->assertSame(
            $retryPolicy->getMaxBackoff(),
            ExponentialWithJitterRetryPolicy::DEFAULT_MAX_BACKOFF
        );
        $this->assertSame(
            $retryPolicy->getMaxAttempts(),
            ExponentialWithJitterRetryPolicy::DEFAULT_MAX_ATTEMPTS
        );
        $this->assertSame(
            $retryPolicy->getJitter(),
            ExponentialWithJitterRetryPolicy::DEFAULT_JITTER
        );
        $this->assertEquals(count($retryPolicy->getRetryableStatusCodes()), 9);
    }

    /**
     * @dataProvider provider
     */
    public function test_delay_is_less_or_equal_to_max_backoff($attempt, $delay, $maxBackoff): void
    {
        $this->assertLessThanOrEqual($maxBackoff, $delay);
    }

    public function provider(): array
    {
        $retryPolicy = new ExponentialWithJitterRetryPolicy(
            $maxAttempts = ExponentialWithJitterRetryPolicy::DEFAULT_MAX_ATTEMPTS,
            $initialBackoff = ExponentialWithJitterRetryPolicy::DEFAULT_INITIAL_BACKOFF,
            $maxBackoff = ExponentialWithJitterRetryPolicy::DEFAULT_MAX_BACKOFF,
            $backoffMultiplier = ExponentialWithJitterRetryPolicy::DEFAULT_BACKOFF_MULTIPLIER,
            $jitter = ExponentialWithJitterRetryPolicy::DEFAULT_JITTER,
            $retryableStatusCodes = [\Grpc\STATUS_CANCELLED],
        );
        $maxBackoff = $retryPolicy->getMaxBackoff();

        return [
            [0, $retryPolicy->getDelay(0)/1000, $maxBackoff],
            [1, $retryPolicy->getDelay(1)/1000, $maxBackoff],
            [2, $retryPolicy->getDelay(2)/1000, $maxBackoff],
            [3, $retryPolicy->getDelay(3)/1000, $maxBackoff],
            [4, $retryPolicy->getDelay(4)/1000, $maxBackoff],
        ];
    }
}
