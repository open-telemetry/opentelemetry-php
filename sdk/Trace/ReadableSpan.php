<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Trace as API;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk.md#additional-span-interfaces
 */
interface ReadableSpan
{
    public function getName(): string;

    public function getContext(): API\SpanContext;

    public function getParentContext(): ?API\SpanContext;

    public function getInstrumentationLibrary(): InstrumentationLibrary;

    public function ended(): bool;

    /**
     * Returns an immutable representation of this instance.
     */
    public function toSpanData();

    /**
     * Returns the duration of the {@see API\Span} in nanoseconds.
     * If still active, returns `now() - start`.
     */
    public function getDuration(): int;

    /**
     * @return API\SpanKind::KIND_*
     */
    public function getKind(): int;

    /**
     * Returns the value of the attribute with the provided *key*.
     * Returns `null` if there are no attributes set, or no attribute with that key exists.
     */
    public function getAttribute(string $key);
}
