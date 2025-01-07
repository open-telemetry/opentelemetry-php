<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use Closure;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * @internal
 */
final class LateBindingTextMapPropagator implements TextMapPropagatorInterface
{
    /**
     * @param TextMapPropagatorInterface|Closure(): TextMapPropagatorInterface $propagator
     */
    public function __construct(
        private TextMapPropagatorInterface|Closure $propagator,
    ) {
    }

    public function fields(): array
    {
        if (!$this->propagator instanceof TextMapPropagatorInterface) {
            $this->propagator = ($this->propagator)();
        }

        return $this->propagator->fields();
    }

    public function inject(mixed &$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
        if (!$this->propagator instanceof TextMapPropagatorInterface) {
            $this->propagator = ($this->propagator)();
        }

        $this->propagator->inject($carrier, $setter, $context);
    }

    public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface
    {
        if (!$this->propagator instanceof TextMapPropagatorInterface) {
            $this->propagator = ($this->propagator)();
        }

        return $this->propagator->extract($carrier, $getter, $context);
    }
}
