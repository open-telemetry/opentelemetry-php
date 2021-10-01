<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use function count;
use OpenTelemetry\Trace as API;

final class Link implements API\Link
{
    private API\Attributes $attributes;
    private API\SpanContext $context;
    private int $totalAttributeCount;

    public function __construct(API\SpanContext $context, API\Attributes $attributes = null)
    {
        $this->context = $context;
        $this->attributes = $attributes ?? new Attributes();
        $this->totalAttributeCount = count($this->attributes);
    }

    public function getSpanContext(): API\SpanContext
    {
        return $this->context;
    }

    public function getAttributes(): API\Attributes
    {
        return $this->attributes;
    }

    public function getTotalAttributeCount(): int
    {
        return $this->totalAttributeCount;
    }
}
