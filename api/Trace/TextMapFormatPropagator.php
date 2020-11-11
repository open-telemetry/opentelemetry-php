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
     * Encodes the given SpanContext into propagator specific format and injects
     * the encoded SpanContext using Setter into it's associated carrier.
     *
     * @param SpanContext $context
     * @param mixed $carrier
     * @param PropagationSetter $setter
     * @return void
     */
    public static function inject(SpanContext $context, &$carrier, PropagationSetter $setter) : void;

    /**
     * Retrieves encoded SpanContext using Getter from the associated carrier.
     *
     * @param mixed $carrier
     * @param PropagationGetter $getter
     * @return SpanContext
     */
    public static function extract($carrier, PropagationGetter $getter): SpanContext;
}
