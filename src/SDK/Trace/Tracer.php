<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function ctype_space;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Trace\Behavior\LoggerAwareTrait;

class Tracer implements API\TracerInterface
{
    use LoggerAwareTrait;

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
            $this->tracerSharedState,
            $this->tracerSharedState->getSpanLimits()
        );
    }

    public function getInstrumentationLibrary(): InstrumentationLibrary
    {
        return $this->instrumentationLibrary;
    }
}
