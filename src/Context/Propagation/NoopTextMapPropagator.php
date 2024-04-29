<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;

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

    public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface
    {
        return $context ?? Context::getCurrent();
    }

    public function inject(&$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
    }
}
