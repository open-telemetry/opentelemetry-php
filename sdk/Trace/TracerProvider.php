<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use function is_array;
use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\Sampler\ParentBased;
use OpenTelemetry\Trace as API;
use function register_shutdown_function;

final class TracerProvider implements API\TracerProvider
{
    /** @var array<string, API\Tracer> */
    private $tracers;

    /** @readonly */
    private TracerSharedState $tracerSharedState;

    /** @param list<SpanProcessor>|SpanProcessor $spanProcessors */
    public function __construct(
        $spanProcessors = [],
        ResourceInfo $resource = null,
        Sampler $sampler = null,
        IdGenerator $idGenerator = null
    ) {
        $spanProcessors = is_array($spanProcessors) ? $spanProcessors : [$spanProcessors];
        $resource = $resource ?? ResourceInfo::defaultResource();
        $sampler = $sampler ?? new ParentBased(new AlwaysOnSampler());
        $idGenerator = $idGenerator ?? new RandomIdGenerator();

        $this->tracerSharedState = new TracerSharedState(
            $idGenerator,
            $resource,
            $sampler,
            $spanProcessors
        );

        register_shutdown_function([$this, 'shutdown']);
    }

    public function shutdown(): void
    {
        if ($this->tracerSharedState->hasShutdown()) {
            return;
        }

        $this->tracerSharedState->shutdown();
    }

    /** @inheritDoc */
    public function getTracer(string $name, ?string $version = null): API\Tracer
    {
        $key = sprintf('%s@%s', $name, ($version ?? 'unknown'));

        if (isset($this->tracers[$key]) && $this->tracers[$key] instanceof API\Tracer) {
            return $this->tracers[$key];
        }

        $instrumentationLibrary = new InstrumentationLibrary($name, $version);

        return $this->tracers[$key] = new Tracer(
            $this->tracerSharedState,
            $instrumentationLibrary,
        );
    }

    public function getSampler(): Sampler
    {
        return $this->tracerSharedState->getSampler();
    }
}
