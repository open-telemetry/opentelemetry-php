<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;

class NullPropagator implements TextMapPropagatorInterface
{
    public function fields(): array
    {
        return [];
    }

    public function inject(&$carrier, PropagationSetterInterface $setter = null, ContextInterface $context = null): void
    {
    }

    public function extract($carrier, PropagationGetterInterface $getter = null, ContextInterface $context = null): ContextInterface
    {
        return $context ?? Context::getCurrent();
    }
}
