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
    private readonly TracerConfig $config;

    public function __construct(
        private readonly TracerSharedState $tracerSharedState,
        private readonly InstrumentationScopeInterface $instrumentationScope,
        ?TracerConfig $config = null,
    ) {
        $this->config = $config ?? TracerConfig::default();
    }

    /** @inheritDoc */
    public function spanBuilder(string $spanName): API\SpanBuilderInterface
    {
        if (ctype_space($spanName)) {
            $spanName = self::FALLBACK_SPAN_NAME;
        }
        if (!$this->config->isEnabled() || $this->tracerSharedState->hasShutdown()) {
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

    public function enabled(): bool
    {
        return $this->config->isEnabled();
    }
}
