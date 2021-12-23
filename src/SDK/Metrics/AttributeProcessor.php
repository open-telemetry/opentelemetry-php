<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context;
use OpenTelemetry\SDK\Attributes;

interface AttributeProcessor {

    public function process(Attributes $attributes, Context $context): Attributes;
}
