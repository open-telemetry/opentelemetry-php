<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SpanMultiProcessor;
use OpenTelemetry\Trace as API;

/**
 * Stores shared state/config between all {@see API\Tracer} created via the same {@see API\TracerProvider}.
 */
final class TracerSharedState
{
    /** @readonly */
    private IdGenerator $idGenerator;

    /** @readonly */
    private ResourceInfo $resource;

    /** @readonly */
    private Sampler $sampler;

    /** @readonly */
    private SpanProcessor $spanProcessor;

    // TODO: Add SpanLimits to constructor

    private bool $hasShutdown = false;

    public function __construct(
        IdGenerator $idGenerator,
        ResourceInfo $resource,
        Sampler $sampler,
        array $spanProcessors
    ) {
        $this->idGenerator = $idGenerator;
        $this->resource = $resource;
        $this->sampler = $sampler;
        $this->spanProcessor = new SpanMultiProcessor($spanProcessors);
    }

    public function hasShutdown(): bool
    {
        return $this->hasShutdown;
    }

    public function getIdGenerator(): IdGenerator
    {
        return $this->idGenerator;
    }

    public function getResource(): ResourceInfo
    {
        return $this->resource;
    }

    public function getSampler(): Sampler
    {
        return $this->sampler;
    }

    public function getSpanProcessor(): SpanProcessor
    {
        return $this->spanProcessor;
    }

    public function shutdown(): void
    {
        $this->spanProcessor->shutdown();
        $this->hasShutdown = true;
    }
}
