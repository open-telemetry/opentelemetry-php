<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use OpenTelemetry\Context\Context;

final class NoopTextMapPropagator implements TextMapPropagatorInterface
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function fields(): array
    {
        return [];
    }

    public function extract($carrier, PropagationGetterInterface $getter = null, Context $context = null): Context
    {
        return $context ?? Context::getRoot();
    }

    public function inject(&$carrier, PropagationSetterInterface $setter = null, Context $context = null): void
    {
    }
}
