<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Retry\RetryPolicyInterface;
use function OpenTelemetry\SDK\Common\Util\isEmpty;
use OpenTelemetry\SDK\Metrics\Exceptions\RetryableExportException;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Throwable;

trait SpanExporterTrait
{
    use LogsMessagesTrait;
    private bool $running = true;
    private ?RetryPolicyInterface $retryPolicy = null;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#shutdown-2 */
    public function shutdown(): bool
    {
        $this->running = false;

        return true;
    }

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#forceflush-2 */
    public function forceFlush(): bool
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
    public function export(iterable $spans): int
    {
        $shouldRetry = true;
        $status = SpanExporterInterface::STATUS_SUCCESS;
        $attempt = 0;
        $exception = null;
        if (!$this->running) {
            return SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE;
        }

        if (isEmpty($spans)) {
            return SpanExporterInterface::STATUS_SUCCESS;
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
                $this->retryPolicy->delay($delay);
                self::logDebug('Retrying span export for ' . $attempt . ' time');
            }
            $attempt++;

            try {
                $status = $this->doExport($spans);
            } catch (Throwable $e) {
                if ($e instanceof \Error) {
                    self::logError($e->getMessage());
                    $e = new RetryableExportException(
                        $e->getMessage(),
                        $e->getCode(),
                        $e
                    );
                    $exception = $e;
                }
            }
            // Find whether the request should be retried or not
            $shouldRetry = $this->retryPolicy->shouldRetry(
                $attempt,
                $status,
                $exception
            );
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

    /**
     * @param iterable<SpanDataInterface> $spans Batch of spans to export
     *
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    abstract protected function doExport(iterable $spans): int; /** @phpstan-ignore-line */
}
