<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final readonly class Link implements LinkInterface
{
    public function __construct(
        private API\SpanContextInterface $context,
        private AttributesInterface $attributes,
    ) {
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
