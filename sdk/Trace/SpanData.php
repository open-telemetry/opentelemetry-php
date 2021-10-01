<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;

/**
 * Represents an immutable snapshot of a {@see API\Span}.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk.md#additional-span-interfaces
 */
interface SpanData
{
    public function getName(): string;
    public function getKind(): int;
    public function getContext(): API\SpanContext;
    public function getParentContext(): API\SpanContext;
    public function getTraceId(): string;
    public function getSpanId(): string;
    public function getParentSpanId(): string;
    public function getStatus(): StatusData;
    public function getStartEpochNanos(): int;
    public function getAttributes(): API\Attributes;

    /** @return list<API\Event> */
    public function getEvents(): array;

    /** @return list<API\Link> */
    public function getLinks(): array;

    public function getEndEpochNanos(): int;
    public function hasEnded(): bool;
    public function getInstrumentationLibrary(): InstrumentationLibrary;
    public function getResource(): ResourceInfo;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk_exporters/non-otlp.md#dropped-events-count */
    public function getTotalDroppedEvents(): int;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk_exporters/non-otlp.md#dropped-links-count */
    public function getTotalDroppedLinks(): int;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk_exporters/non-otlp.md#dropped-attributes-count */
    public function getTotalDroppedAttributes(): int;
}
