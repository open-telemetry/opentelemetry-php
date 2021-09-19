<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk.md#additional-span-interfaces
 */
interface ReadableSpan
{
    public function getSpanName(): string;

    public function getContext(): API\SpanContext;

    public function getSpanKind(): int;

    public function getStartEpochTimestamp(): int;

    public function getEndEpochTimestamp(): ?int;

    public function getAttributes(): API\Attributes;

    public function getLinks(): API\Links;

    public function getDroppedLinksCount(): int;

    public function getEvents(): API\Events;

    public function getDroppedEventsCount(): int;

    public function getStatus(): API\SpanStatus;

    public function getResource(): ResourceInfo;

    public function getInstrumentationLibrary(): InstrumentationLibrary;

    public function ended(): bool;

    // ambiguous methods
    public function getDuration(): ?int;

    public function getParent(): ?API\SpanContext;

    public function getStart(): int;

    public function getEnd(): ?int;
}
