<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class Link implements API\Link
{
    private $attributes;
    private $context;

    public function __construct(API\SpanContext $context, ?API\Attributes $attributes = null)
    {
        $this->attributes = $attributes ?? new Attributes();
        $this->context = $context;
    }

    public function getContext(): API\SpanContext
    {
        return $this->context;
    }

    public function getAttributes(): API\Attributes
    {
        return $this->attributes;
    }
}
