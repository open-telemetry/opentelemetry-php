<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use OpenTelemetry\Context\ContextInterface;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#textmap-propagator
 */
interface TextMapPropagatorInterface
{
    /**
     * Returns list of fields that will be used by this propagator.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#fields
     *
     * @return list<string>
     */
    public function fields() : array;

    /**
     * Injects specific values from the provided {@see ContextInterface} into the provided carrier
     * via an {@see PropagationSetterInterface}.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#textmap-inject
     */
    public function inject(mixed &$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void;

    /**
     * Extracts specific values from the provided carrier into the provided {@see ContextInterface}
     * via an {@see PropagationGetterInterface}.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#textmap-extract
     */
    public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface;
}
