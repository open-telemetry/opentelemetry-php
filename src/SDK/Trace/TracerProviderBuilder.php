<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use OpenTelemetry\SDK\Resource\ResourceInfo;

class TracerProviderBuilder
{
    /** @var list<SpanProcessorInterface> */
    private ?array $spanProcessors = [];
    private ?ResourceInfo $resource = null;
    private ?SamplerInterface $sampler = null;
    /** @var list<Condition> */
    private array $conditions = [];

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

    public function addTracerConfiguratorCondition(Predicate $predicate, State $state): self
    {
        $this->conditions[] = new Condition($predicate, $state);

        return $this;
    }

    public function build(): TracerProviderInterface
    {
        return new TracerProvider(
            $this->spanProcessors,
            $this->sampler,
            $this->resource,
            configurator: new TracerConfigurator($this->conditions),
        );
    }
}
