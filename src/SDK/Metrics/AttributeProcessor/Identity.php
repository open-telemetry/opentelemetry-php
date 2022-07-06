<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;

final class Identity implements AttributeProcessor
{
    public function process(AttributesInterface $attributes, Context $context): AttributesInterface
    {
        return $attributes;
    }
}
