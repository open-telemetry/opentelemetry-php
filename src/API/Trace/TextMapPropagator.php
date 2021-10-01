<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\Context\Context;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#textmap-propagator
 */
interface TextMapPropagator
{
    /**
     * Returns list of fields that will be used by this propagator.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#fields
     *
     * @return list<string>
     */
    public static function fields() : array;

    /**
     * Injects specific values from the provided {@see Context} into the provided carrier
     * via an {@see PropagationSetter}.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#textmap-inject
     *
     * @param mixed $carrier
     */
    public static function inject(&$carrier, PropagationSetter $setter = null, Context $context = null): void;

    /**
     * Extracts specific values from the provided carrier into the provided {@see Context}
     * via an {@see PropagationGetter}.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/api-propagators.md#textmap-extract
     */
    public static function extract($carrier, PropagationGetter $getter = null, Context $context = null): Context;
}
