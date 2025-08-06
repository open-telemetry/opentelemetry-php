<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\SDK\Common\Services\SpiLoadableInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

interface SpanProcessorFactoryInterface extends SpiLoadableInterface
{
    public function create(SpanProcessorContext $context): SpanProcessorInterface;
}
