<?php

declare(strict_types=1);

namespace OpenTelemetry\Propagation\HTTPTextFormat;

use OpenTelemetry\Context\SpanContext;
use OpenTelemetry\Propagation\Getter\GetterInterface;
use OpenTelemetry\Propagation\Setter\SetterInterface;

interface HTTPTextFormatInterface
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
     * @param SetterInterface $setter
     * @return void
     */
    public function inject(SpanContext $context, $carrier, SetterInterface $setter) : void;

    /**
     * Retrieves encoded SpanContext using Getter from the associated carrier.
     *
     * @param GetterInterface $getter
     * @return SpanContext
     */
    public function extract(GetterInterface $getter): SpanContext;
}