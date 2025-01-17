<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Services\SpiLoadableInterface;

interface TextMapPropagatorFactoryInterface extends SpiLoadableInterface
{
    public function create(): TextMapPropagatorInterface;
}
