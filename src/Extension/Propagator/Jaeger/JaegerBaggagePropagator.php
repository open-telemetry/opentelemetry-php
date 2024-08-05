<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\Jaeger;

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Baggage\Entry; /** @phan-suppress-current-line PhanUnreferencedUseNormal */
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * JaegerBaggagePropagator is a baggage propagator that supports the specification for the header
 * "uberctx" used for baggage propagation.
 * (https://www.jaegertracing.io/docs/1.52/client-libraries/#baggage)
 */
class JaegerBaggagePropagator implements TextMapPropagatorInterface
{
    private const UBER_BAGGAGE_HEADER_PREFIX = 'uberctx-';

    private static ?TextMapPropagatorInterface $instance = null;

    public static function getInstance(): TextMapPropagatorInterface
    {
        if (self::$instance === null) {
            self::$instance = new JaegerBaggagePropagator();
        }

        return self::$instance;
    }

    public function fields(): array
    {
        return [];
    }

    /** {@inheritdoc} */
    public function inject(&$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
        $setter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $baggage = Baggage::fromContext($context);

        if ($baggage->isEmpty()) {
            return;
        }

        /** @var Entry $entry */
        foreach ($baggage->getAll() as $key => $entry) {
            $key = self::UBER_BAGGAGE_HEADER_PREFIX . $key;
            $value = rawurlencode((string) $entry->getValue());
            $setter->set($carrier, $key, $value);
        }
    }

    /** {@inheritdoc} */
    public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $baggageKeys = $getter->keys($carrier);

        if ($baggageKeys === []) {
            return $context;
        }

        $baggageBuilder = Baggage::getBuilder();

        foreach ($baggageKeys as $key) {
            if (str_starts_with($key, self::UBER_BAGGAGE_HEADER_PREFIX)) {
                $baggageKey = substr($key, strlen(self::UBER_BAGGAGE_HEADER_PREFIX));
                $value = $getter->get($carrier, $key) ?? '';
                $baggageBuilder->set($baggageKey, rawurldecode($value));
            }
        }

        return $context->withContextValue($baggageBuilder->build());
    }
}
