<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Trace as API;

interface SpanData
{
    public function getName(): string;
    public function getKind(): API\SpanKind;
    public function getContext(): API\SpanContext;
    public function getParentContext(): API\SpanContext;
    public function getTraceId(): string;
    public function getSpanId(): string;
    public function getParentSpanId(): string;
    public function getStatus(): API\StatusCode;
    public function getStartEpochNanos(): int;
    public function getAttributes(): API\Attributes;
    public function getEvents(): API\Events;
    public function getLinks(): API\Links;
    public function getEndEpochNanos(): int;
    public function getInstrumentationLibrary(): InstrumentationLibrary;
    public function getResource(): ResourceInfo;

    // TODO: Track total total amount of recorded events, links, and attributes.
}
