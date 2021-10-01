<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

interface Link
{
    public function getSpanContext(): SpanContext;
    public function getAttributes(): Attributes;
    public function getTotalAttributeCount(): int;
}
