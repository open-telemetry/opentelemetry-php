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
 * (https://github.com/openzipkin/b3-propagation#single-header)
 */
final class B3Propagator implements TextMapPropagatorInterface
{
    private TextMapPropagatorInterface $propagator;

    private static ?self $instance = null;

    private function __construct()
    {
        $propagatorList = getenv('OTEL_PROPAGATORS');
        /**
         * @phpstan-ignore-next-line
         */
        if ((is_array($propagatorList) && in_array('b3Multi', $propagatorList)) || ('b3Multi' === $propagatorList)) {
            $this->propagator = B3MultiPropagator::getInstance();
        } else {
            $this->propagator = B3SinglePropagator::getInstance();
        }
    }

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
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
