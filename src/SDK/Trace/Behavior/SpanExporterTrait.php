<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;

trait SpanExporterTrait
{
    private ?SpanConverterInterface $converter = null;
    private bool $running = true;

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
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    abstract public function export(iterable $spans): int;
}
