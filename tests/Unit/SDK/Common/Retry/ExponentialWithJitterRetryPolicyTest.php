<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Retry;

use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OtlpExporter;
use OpenTelemetry\SDK\Common\Retry\ExponentialWithJitterRetryPolicy;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @covers \OpenTelemetry\SDK\Common\Retry\ExponentialWithJitterRetryPolicy
 */
class ExponentialWithJitterRetryPolicyTest extends TestCase
{
    private OtlpExporter $exporter;
    // private ClientInterface $client;

    public function setUp(): void
    {
        $request = $this->createMock(RequestInterface::class);
        // $this->client = $this->createMock(ClientInterface::class);
        $this->exporter = $this->createExporter();
    }

    private function createExporter(): OtlpExporter
    {
        return new class() extends OtlpExporter {
        };
    }

    public function test_retry_policy_set_properly()
    {
        $this->exporter->setRetryPolicy(ExponentialWithJitterRetryPolicy::getDefault());
        $this->assertSame(
            $this->exporter->getRetryPolicy()->getMaxAttempts(),
            ExponentialWithJitterRetryPolicy::DEFAULT_MAX_ATTEMPTS
        );
        $this->assertSame(
            (int) ($this->exporter->getRetryPolicy()->getInitialBackoff()),
            ExponentialWithJitterRetryPolicy::DEFAULT_INITIAL_BACKOFF
        );
        $this->assertSame(
            $this->exporter->getRetryPolicy()->getMaxBackoff(),
            ExponentialWithJitterRetryPolicy::DEFAULT_MAX_BACKOFF
        );
        $this->assertSame(
            $this->exporter->getRetryPolicy()->getMaxAttempts(),
            ExponentialWithJitterRetryPolicy::DEFAULT_MAX_ATTEMPTS
        );
        $this->assertSame(
            $this->exporter->getRetryPolicy()->getJitter(),
            ExponentialWithJitterRetryPolicy::DEFAULT_JITTER
        );
    }

    public function test_delay_is_less_or_equal_to_max_backoff()
    {
        // $exporter = $this->createMock(OtlpExporter::class);
        $this->exporter->setRetryPolicy(ExponentialWithJitterRetryPolicy::getDefault());
        $retryPolicy = $this->exporter->getRetryPolicy();
        $maxAttempts = $retryPolicy->getMaxAttempts();
        for ($i=0; $i < $maxAttempts; $i++) {
            $delay = floor($retryPolicy->getDelay($i)/1000);
            echo 'delay: ' . (string) $delay . PHP_EOL;
            $this->assertLessThanOrEqual($retryPolicy->getMaxBackoff(), $delay);
        }
    }
}
