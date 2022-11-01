<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Resource\ResourceInfo;

class TracerProviderBuilder
{
    // @var array<SpanProcessorInterface>
    private ?array $spanProcessors = [];
    private ?ResourceInfo $resource = null;
    private ?SamplerInterface $sampler = null;

    public function addSpanProcessor(SpanProcessorInterface $spanProcessor): self
    {
        $this->spanProcessors[] = $spanProcessor;

        return $this;
    }

    public function setResource(ResourceInfo $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function setSampler(SamplerInterface $sampler): self
    {
        $this->sampler = $sampler;

        return $this;
    }

    public function build(): TracerProviderInterface
    {
        return new TracerProvider(
            $this->spanProcessors,
            $this->sampler,
            $this->resource,
        );
    }
}
