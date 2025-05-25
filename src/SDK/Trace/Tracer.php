<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function ctype_space;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressor;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressor;

class Tracer implements API\TracerInterface
{
    public const FALLBACK_SPAN_NAME = 'empty';
    private Config $config;

    public function __construct(
        private readonly TracerSharedState $tracerSharedState,
        private readonly InstrumentationScopeInterface $instrumentationScope,
        ?Configurator $configurator = null,
        private readonly SpanSuppressor $spanSuppressor = new NoopSuppressor(),
    ) {
        $this->config = $configurator ? $configurator->resolve($this->instrumentationScope) : TracerConfig::default();
    }

    /** @inheritDoc */
    #[\Override]
    public function spanBuilder(string $spanName): API\SpanBuilderInterface
    {
        if (ctype_space($spanName)) {
            $spanName = self::FALLBACK_SPAN_NAME;
        }
        // If a Tracer is disabled, it MUST behave equivalently to No-op Tracer
        if (!$this->config->isEnabled() || $this->tracerSharedState->hasShutdown()) {
            return new API\NoopSpanBuilder(Context::storage());
        }

        return new SpanBuilder(
            $spanName,
            $this->instrumentationScope,
            $this->tracerSharedState,
            $this->spanSuppressor,
        );
    }

    public function getInstrumentationScope(): InstrumentationScopeInterface
    {
        return $this->instrumentationScope;
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function updateConfig(Configurator $configurator): void
    {
        $this->config = $configurator->resolve($this->instrumentationScope);
    }
}
