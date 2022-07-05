<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use Closure;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;

final class Filtered implements AttributeProcessor
{
    private AttributesFactory $attributes;
    private Closure $filter;

    public function __construct(AttributesFactory $attributes, Closure $filter)
    {
        $this->attributes = $attributes;
        $this->filter = $filter;
    }

    public function process(Attributes $attributes, Context $context): Attributes
    {
        $filtered = $this->attributes->builder();
        foreach ($attributes as $key => $value) {
            if (($this->filter)($key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered->build();
    }
}
