<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

interface LinkInterface
{
    public function getSpanContext(): SpanContextInterface;
    public function getAttributes(): AttributesInterface;
    public function getTotalAttributeCount(): int;
}
