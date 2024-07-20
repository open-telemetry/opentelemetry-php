<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function ctype_space;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;

class Tracer implements API\TracerInterface
{
    public const FALLBACK_SPAN_NAME = 'empty';
    private Config $config;

    public function __construct(
        private readonly TracerSharedState $tracerSharedState,
        private readonly InstrumentationScopeInterface $instrumentationScope,
        ?Config $config = null,
    ) {
        $this->config = $config ?? Config::default();
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

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function updateConfig(Configurator $configurator): void
    {
        $this->config = $configurator->getConfig($this->instrumentationScope);
    }
}
