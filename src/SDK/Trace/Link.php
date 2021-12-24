<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function count;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;

final class Link implements LinkInterface
{
    private AttributesInterface $attributes;
    private API\SpanContextInterface $context;
    private int $totalAttributeCount;

    public function __construct(API\SpanContextInterface $context, AttributesInterface $attributes = null)
    {
        $this->context = $context;
        $this->attributes = $attributes ?? new Attributes();
        $this->totalAttributeCount = count($this->attributes);
    }

    public function getSpanContext(): API\SpanContextInterface
    {
        return $this->context;
    }

    public function getAttributes(): AttributesInterface
    {
        return $this->attributes;
    }

    public function getTotalAttributeCount(): int
    {
        return $this->totalAttributeCount;
    }
}
