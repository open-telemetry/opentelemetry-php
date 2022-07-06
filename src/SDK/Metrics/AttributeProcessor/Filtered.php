<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use Closure;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;

final class Filtered implements AttributeProcessorInterface
{
    private AttributesFactoryInterface $attributesFactory;
    private Closure $filter;

    public function __construct(AttributesFactoryInterface $attributesFactory, Closure $filter)
    {
        $this->attributesFactory = $attributesFactory;
        $this->filter = $filter;
    }

    public function process(AttributesInterface $attributes, Context $context): AttributesInterface
    {
        $filtered = $this->attributesFactory->builder();
        foreach ($attributes as $key => $value) {
            if (($this->filter)($key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered->build();
    }
}
