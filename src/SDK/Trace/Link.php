<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class Link implements LinkInterface
{
    private AttributesInterface $attributes;
    private API\SpanContextInterface $context;

    public function __construct(API\SpanContextInterface $context, AttributesInterface $attributes)
    {
        $this->context = $context;
        $this->attributes = $attributes;
    }

    public function getSpanContext(): API\SpanContextInterface
    {
        return $this->context;
    }

    public function getAttributes(): AttributesInterface
    {
        return $this->attributes;
    }
}
