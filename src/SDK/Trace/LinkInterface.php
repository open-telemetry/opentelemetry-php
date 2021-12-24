<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\AttributesInterface;
use OpenTelemetry\API\Trace\SpanContextInterface;

interface LinkInterface
{
    public function getSpanContext(): SpanContextInterface;
    public function getAttributes(): AttributesInterface;
    public function getTotalAttributeCount(): int;
}
