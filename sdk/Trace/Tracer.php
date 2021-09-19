<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Trace as API;

class Tracer implements API\Tracer
{
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
    public function spanBuilder(string $spanName): API\SpanBuilder
    {
        if ($this->tracerSharedState->hasShutdown()) {
            // TODO: Return a noop tracer
        }

        return new SpanBuilder(
            $spanName,
            $this->instrumentationLibrary,
            $this->tracerSharedState,
        );
    }

    public function getInstrumentationLibrary(): InstrumentationLibrary
    {
        return $this->instrumentationLibrary;
    }
}
