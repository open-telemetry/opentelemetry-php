<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Message;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

interface FactoryDecoratorInterface
{
    public function getPropagator(): TextMapPropagatorInterface;
}
