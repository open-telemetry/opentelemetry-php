<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Retry\RetryPolicyInterface;
use OpenTelemetry\SDK\Common\Time\SchedulerInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

trait SpanExporterTrait
{
    use LogsMessagesTrait;
    private bool $running = true;
    private ?RetryPolicyInterface $retryPolicy = null;
    private SchedulerInterface $delayScheduler;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#shutdown-2 */
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        $this->running = false;

        return true;
    }

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#forceflush-2 */
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    abstract public static function fromConnectionString(string $endpointUrl, string $name, string $args);

    /**
     * @param iterable<SpanDataInterface> $spans Batch of spans to export
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#exportbatch
     *
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    public function export(iterable $spans, ?CancellationInterface $cancellation = null): int
    {
        $shouldRetry = true;
        $status = SpanExporterInterface::STATUS_SUCCESS;
        $attempt = 0;
        if (!$this->running) {
            return SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE;
        }

        // If retryPolicy is not set then just doExport once and return the status
        if ($this->retryPolicy === null) {
            self::logDebug('retry polict is null. Exporting the spans without retry');

            return $this->doExport($spans); /** @phpstan-ignore-line */
        }

        while ($shouldRetry) {
            // find the delay time before retry (it is 0 for the first attempt)
            $delay = $this->retryPolicy->getDelay($attempt);
            if ($attempt > 0) {
                self::logDebug('Waiting for ' . $delay . ' mili seconds before retrying export');
                $this->delayScheduler->delay($delay);
                self::logDebug('Retrying span export for ' . $attempt . ' time');
            }
            $attempt++;
            $status = $this->doExport($spans); /** @phpstan-ignore-line */
            $shouldRetry = $this->retryPolicy->shouldRetry($attempt, $status);
        }

        return $status; /** @phpstan-ignore-line */
    }

    public function setRetryPolicy(RetryPolicyInterface $retryPolicy)
    {
        $this->retryPolicy = $retryPolicy;
    }

    public function getRetryPolicy(): ?RetryPolicyInterface
    {
        return $this->retryPolicy;
    }

    public function setDelayScheduler(SchedulerInterface $delayScheduler)
    {
        $this->delayScheduler = $delayScheduler;
    }

    public function getDelayScheduler(): ?SchedulerInterface
    {
        return $this->delayScheduler;
    }

    /**
     * @param iterable<SpanDataInterface> $spans Batch of spans to export
     *
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    abstract protected function doExport(iterable $spans): int; /** @phpstan-ignore-line */
}
