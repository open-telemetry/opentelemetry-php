<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function count;
use OpenTelemetry\API\Trace as API;

final class Link implements API\LinkInterface
{
    private API\AttributesInterface $attributes;
    private API\SpanContextInterface $context;
    private int $totalAttributeCount;

    public function __construct(API\SpanContextInterface $context, API\AttributesInterface $attributes = null)
    {
        $this->context = $context;
        $this->attributes = $attributes ?? new Attributes();
        $this->totalAttributeCount = count($this->attributes);
    }

    public function getSpanContext(): API\SpanContextInterface
    {
        return $this->context;
    }

    public function getAttributes(): API\AttributesInterface
    {
        return $this->attributes;
    }

    public function getTotalAttributeCount(): int
    {
        return $this->totalAttributeCount;
    }
}
