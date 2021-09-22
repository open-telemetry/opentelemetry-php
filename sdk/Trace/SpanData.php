<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;

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
    public function getEvents(): API\Events;
    public function getLinks(): API\Links;
    public function getEndEpochNanos(): int;
    public function getInstrumentationLibrary(): InstrumentationLibrary;
    public function getResource(): ResourceInfo;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk_exporters/non-otlp.md#dropped-events-count */
    public function getTotalDroppedEvents(): int;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk_exporters/non-otlp.md#dropped-links-count */
    public function getTotalDroppedLinks(): int;

    /** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk_exporters/non-otlp.md#dropped-attributes-count */
    public function getTotalDroppedAttributes(): int;
}
