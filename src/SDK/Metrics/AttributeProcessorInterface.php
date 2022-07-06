<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

interface AttributeProcessorInterface
{
    public function process(AttributesInterface $attributes, Context $context): AttributesInterface;
}
