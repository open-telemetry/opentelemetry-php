<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;

final class IdentityAttributeProcessor implements AttributeProcessorInterface
{
    public function process(AttributesInterface $attributes, Context $context): AttributesInterface
    {
        return $attributes;
    }
}
