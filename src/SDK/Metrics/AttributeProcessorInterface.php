<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * @internal
 */
interface AttributeProcessorInterface
{
    public function process(AttributesInterface $attributes, ContextInterface $context): AttributesInterface;
}
