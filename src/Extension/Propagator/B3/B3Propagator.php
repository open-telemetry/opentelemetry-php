<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\B3;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * B3 is a propagator that supports the specification for the header
 * "b3" used for trace context propagation across service boundaries.
 * (https://github.com/openzipkin/b3-propagation)
 */
final class B3Propagator implements TextMapPropagatorInterface
{
    private function __construct(private readonly TextMapPropagatorInterface $propagator)
    {
    }

    public static function getB3SingleHeaderInstance(): self
    {
        static $instance;

        return $instance ??= new self(B3SinglePropagator::getInstance());
    }
    public static function getB3MultiHeaderInstance(): self
    {
        static $instance;

        return $instance ??= new self(B3MultiPropagator::getInstance());
    }

    /** {@inheritdoc} */
    #[\Override]
    public function fields(): array
    {
        return $this->propagator->fields();
    }

    /** {@inheritdoc} */
    #[\Override]
    public function inject(&$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
        $this->propagator->inject($carrier, $setter, $context);
    }

    /** {@inheritdoc} */
    #[\Override]
    public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $b3SingleHeaderContext = B3SinglePropagator::getInstance()->extract($carrier, $getter, $context);
        if ($b3SingleHeaderContext !== $context) {
            return $b3SingleHeaderContext;
        }

        return B3MultiPropagator::getInstance()->extract($carrier, $getter, $context);
    }
}
