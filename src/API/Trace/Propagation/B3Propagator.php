<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\Propagation;

use OpenTelemetry\Context\Context;
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
    private TextMapPropagatorInterface $propagator;

    private function __construct(TextMapPropagatorInterface $propagator)
    {
        $this->propagator = $propagator;
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
    public function fields(): array
    {
        return $this->propagator->fields();
    }

    /** {@inheritdoc} */
    public function inject(&$carrier, PropagationSetterInterface $setter = null, Context $context = null): void
    {
        $this->propagator->inject($carrier, $setter, $context);
    }

    /** {@inheritdoc} */
    public function extract($carrier, PropagationGetterInterface $getter = null, Context $context = null): Context
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $b3Context = $getter->get($carrier, B3SinglePropagator::B3);

        if ($b3Context) {
            return B3SinglePropagator::getInstance()->extract($carrier, $getter, $context);
        }

        return B3MultiPropagator::getInstance()->extract($carrier, $getter, $context);
    }
}
