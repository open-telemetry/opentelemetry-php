<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function ctype_space;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

class Tracer implements API\TracerInterface
{
    public const FALLBACK_SPAN_NAME = 'empty';

    /** @readonly */
    private TracerSharedState $tracerSharedState;

    /** @readonly */
    private InstrumentationScopeInterface $instrumentationScope;

    public function __construct(
        TracerSharedState $tracerSharedState,
        InstrumentationScopeInterface $instrumentationScope
    ) {
        $this->tracerSharedState = $tracerSharedState;
        $this->instrumentationScope = $instrumentationScope;
    }

    /** @inheritDoc */
    public function spanBuilder(string $spanName): API\SpanBuilderInterface
    {
        if (ctype_space($spanName)) {
            $spanName = self::FALLBACK_SPAN_NAME;
        }

        if ($this->tracerSharedState->hasShutdown()) {
            return new API\NoopSpanBuilder(Context::storage());
        }

        return new SpanBuilder(
            $spanName,
            $this->instrumentationScope,
            $this->tracerSharedState,
        );
    }

    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->instrumentationScope;
    }
}
