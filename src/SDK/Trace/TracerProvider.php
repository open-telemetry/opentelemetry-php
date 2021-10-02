<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function is_array;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use function register_shutdown_function;

final class TracerProvider implements API\TracerProviderInterface
{
    /** @var array<string, API\TracerInterface> */
    private $tracers;

    /** @readonly */
    private TracerSharedState $tracerSharedState;

    /** @param list<SpanProcessorInterface>|SpanProcessorInterface|null $spanProcessors */
    public function __construct(
        $spanProcessors = [],
        SamplerInterface $sampler = null,
        ResourceInfo $resource = null,
        SpanLimits $spanLimits = null,
        IdGeneratorInterface $idGenerator = null
    ) {
        if (null === $spanProcessors) {
            $spanProcessors = [];
        }

        $spanProcessors = is_array($spanProcessors) ? $spanProcessors : [$spanProcessors];
        $resource = $resource ?? ResourceInfo::defaultResource();
        $sampler = $sampler ?? new ParentBased(new AlwaysOnSampler());
        $idGenerator = $idGenerator ?? new RandomIdGenerator();
        $spanLimits = $spanLimits ?? (new SpanLimitsBuilder())->build();

        $this->tracerSharedState = new TracerSharedState(
            $idGenerator,
            $resource,
            $spanLimits,
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
    public function getTracer(string $name, ?string $version = null): API\TracerInterface
    {
        $key = sprintf('%s@%s', $name, ($version ?? 'unknown'));

        if (isset($this->tracers[$key]) && $this->tracers[$key] instanceof API\TracerInterface) {
            return $this->tracers[$key];
        }

        $instrumentationLibrary = new InstrumentationLibrary($name, $version);

        return $this->tracers[$key] = new TracerInterface(
            $this->tracerSharedState,
            $instrumentationLibrary,
        );
    }

    public function getSampler(): SamplerInterface
    {
        return $this->tracerSharedState->getSampler();
    }
}
