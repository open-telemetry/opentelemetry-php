<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class TracerProviderBuilder
{
    /** @var list<SpanProcessorInterface> */
    private ?array $spanProcessors = [];
    private ?ResourceInfo $resource = null;
    private ?SamplerInterface $sampler = null;
    private ?Configurator $configurator = null;

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

    public function setConfigurator(Configurator $configurator): self
    {
        $this->configurator = $configurator;

        return $this;
    }

    public function build(): TracerProviderInterface
    {
        return new TracerProvider(
            $this->spanProcessors,
            $this->sampler,
            $this->resource,
            configurator: $this->configurator ?? Configurator::tracer(),
        );
    }
}
