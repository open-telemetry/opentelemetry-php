<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;

/**
 * @internal
 */
final class IdentityAttributeProcessor implements AttributeProcessorInterface
{
    public function process(AttributesInterface $attributes, ContextInterface $context): AttributesInterface
    {
        return $attributes;
    }
}
