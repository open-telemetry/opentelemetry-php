<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function ctype_space;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\InstrumentationLibrary;

class Tracer implements API\TracerInterface
{
    public const FALLBACK_SPAN_NAME = 'empty';

    /** @readonly */
    private TracerSharedState $tracerSharedState;

    /** @readonly */
    private InstrumentationLibrary $instrumentationLibrary;

    public function __construct(
        TracerSharedState $tracerSharedState,
        InstrumentationLibrary $instrumentationLibrary
    ) {
        $this->tracerSharedState = $tracerSharedState;
        $this->instrumentationLibrary = $instrumentationLibrary;
    }

    /** @inheritDoc */
    public function spanBuilder(string $spanName): API\SpanBuilderInterface
    {
        if (ctype_space($spanName)) {
            $spanName = self::FALLBACK_SPAN_NAME;
        }

        if ($this->tracerSharedState->hasShutdown()) {
            // TODO: Return a noop tracer
        }

        return new SpanBuilder(
            $spanName,
            $this->instrumentationLibrary,
            $this->tracerSharedState->getResource(),
            $this->tracerSharedState->getSampler(),
            $this->tracerSharedState->getSpanProcessor(),
            $this->tracerSharedState->getIdGenerator(),
            $this->tracerSharedState->getSpanLimits()->getSpanAttributes()->builder(),
            $this->tracerSharedState->getSpanLimits()->getLinkAttributes(),
            $this->tracerSharedState->getSpanLimits()->getEventAttributes(),
            $this->tracerSharedState->getSpanLimits()->getLinkCountLimit(),
            $this->tracerSharedState->getSpanLimits()->getEventCountLimit(),
        );
    }

    public function getInstrumentationLibrary(): InstrumentationLibrary
    {
        return $this->instrumentationLibrary;
    }
}
