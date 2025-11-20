<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

/**
 * @experimental
 */
interface ExtendedSpanProcessorInterface extends SpanProcessorInterface
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.51.0/specification/trace/sdk.md#onending
     */
    public function onEnding(ReadWriteSpanInterface $span): void;
}
