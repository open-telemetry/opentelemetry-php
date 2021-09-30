<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SpanMultiProcessor;
use OpenTelemetry\Trace as API; /** @phan-suppress-current-line PhanUnreferencedUseNormal */

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
    private SpanLimits $spanLimits;

    /** @readonly */
    private Sampler $sampler;

    /** @readonly */
    private SpanProcessor $spanProcessor;

    private bool $hasShutdown = false;

    public function __construct(
        IdGenerator $idGenerator,
        ResourceInfo $resource,
        SpanLimits $spanLimits,
        Sampler $sampler,
        array $spanProcessors
    ) {
        $this->idGenerator = $idGenerator;
        $this->resource = $resource;
        $this->spanLimits = $spanLimits;
        $this->sampler = $sampler;

        switch (count($spanProcessors)) {
            case 0:
                $this->spanProcessor = NoopSpanProcessor::getInstance();

                break;
            case 1:
                $this->spanProcessor = $spanProcessors[0];

                break;
            default:
                $this->spanProcessor = new SpanMultiProcessor($spanProcessors);

                break;
        }
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

    public function getSpanLimits(): SpanLimits
    {
        return $this->spanLimits;
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
