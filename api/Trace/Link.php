<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Link
{
    public function getSpanContext(): SpanContext;
    public function getAttributes(): Attributes;
}
