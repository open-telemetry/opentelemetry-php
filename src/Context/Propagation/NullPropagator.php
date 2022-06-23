<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use OpenTelemetry\Context\Context;

class NullPropagator implements TextMapPropagatorInterface
{
    public function fields(): array
    {
        return [];
    }

    public function inject(&$carrier, PropagationSetterInterface $setter = null, Context $context = null): void
    {
    }

    public function extract($carrier, PropagationGetterInterface $getter = null, Context $context = null): Context
    {
        return $context ?? Context::getCurrent();
    }
}
