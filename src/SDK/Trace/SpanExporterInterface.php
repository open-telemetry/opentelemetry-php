<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#span-exporter
 */
interface SpanExporterInterface
{
    /**
     * Possible return values as outlined in the OpenTelemetry spec
     */
    public const STATUS_SUCCESS = 0;
    public const STATUS_FAILED_NOT_RETRYABLE = 1;
    public const STATUS_FAILED_RETRYABLE = 2;

    public static function fromConnectionString(string $endpointUrl, string $name, string $args);

    /**
     * @param iterable<SpanDataInterface> $spans Batch of spans to export
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#exportbatch
     *
     * @return SpanExporterInterface::STATUS_*
     */
    public function export(iterable $spans): int;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#shutdown-2 */
    public function shutdown(): bool;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/sdk.md#forceflush-2 */
    public function forceFlush(): bool;
}
