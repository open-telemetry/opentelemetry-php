<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

class NoopTextMapPropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return NoopTextMapPropagator::getInstance();
    }

    public function type(): string
    {
        return 'none';
    }

    public function priority(): int
    {
        return 0;
    }
}
