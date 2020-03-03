<?php

declare(strict_types=1);

namespace OpenTelemetry\Propagation;

use OpenTelemetry\Context\SpanContext;

interface HTTPTextFormat
{
    /**
     * Returns list of fields that will be used by this formatter.
     *
     * @return array
     */
    public function fields() : array;

    /**
     * Encodes the given SpanContext into propagator specific format and injects
     * the encoded SpanContext using Setter into it's associated carrier.
     *
     * @param SpanContext $context
     * @param mixed $carrier
     * @param Setter $setter
     * @return void
     */
    public function inject(SpanContext $context, $carrier, Setter $setter) : void;

    /**
     * Retrieves encoded SpanContext using Getter from the associated carrier.
     *
     * @param mixed $carrier
     * @param Getter $getter
     * @return SpanContext
     */
    public function extract($carrier, Getter $getter): SpanContext;
}
