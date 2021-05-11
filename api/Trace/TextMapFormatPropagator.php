<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface TextMapFormatPropagator
{
    /**
     * Returns list of fields that will be used by this formatter.
     *
     * @return array
     */
    public static function fields() : array;

    /**
     * Encodes the given Baggage into propagator specific format and injects
     * the encoded Baggage using Setter into it's associated carrier.
     *
     * @param Baggage $context
     * @param mixed $carrier
     * @param PropagationSetter $setter
     * @return void
     */
    public static function inject(Baggage $context, &$carrier, PropagationSetter $setter) : void;

    /**
     * Retrieves encoded Baggage using Getter from the associated carrier.
     *
     * @param mixed $carrier
     * @param PropagationGetter $getter
     * @return Baggage
     */
    public static function extract($carrier, PropagationGetter $getter): Baggage;
}
