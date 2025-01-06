<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class Link implements LinkInterface
{
    public function __construct(
        private readonly API\SpanContextInterface $context,
        private readonly AttributesInterface $attributes,
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
