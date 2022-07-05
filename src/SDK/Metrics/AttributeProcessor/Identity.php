<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;

final class Identity implements AttributeProcessor
{
    public function process(Attributes $attributes, Context $context): Attributes
    {
        return $attributes;
    }
}
