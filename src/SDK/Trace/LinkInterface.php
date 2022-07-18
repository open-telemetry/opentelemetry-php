<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

interface LinkInterface
{
    public function getSpanContext(): SpanContextInterface;
    public function getAttributes(): AttributesInterface;
}
