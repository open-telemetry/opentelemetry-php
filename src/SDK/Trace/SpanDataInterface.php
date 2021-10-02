<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource\ResourceInfo;

/**
 * Represents an immutable snapshot of a {@see API\SpanInterface}.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk.md#additional-span-interfaces
 */
interface SpanDataInterface
{
    public function getName(): string;
    public function getKind(): int;
    public function getContext(): API\SpanContextInterface;
    public function getParentContext(): API\SpanContextInterface;
    public function getTraceId(): string;
    public function getSpanId(): string;
    public function getParentSpanId(): string;
    public function getStatus(): StatusData;
    public function getStartEpochNanos(): int;
    public function getAttributes(): API\AttributesInterface;

    /** @return list<API\EventInterface> */
    public function getEvents(): array;

    /** @return list<API\LinkInterface> */
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
