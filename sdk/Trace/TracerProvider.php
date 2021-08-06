<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\Sampler\ParentBased;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SpanMultiProcessor;
use OpenTelemetry\Trace as API;

final class TracerProvider implements API\TracerProvider
{
    /**
     * @var Tracer[]
     */
    protected $tracers;

    /**
     * @var IdGenerator
     */
    protected $idGenerator;

    /**
     * @var SpanMultiProcessor
     */
    protected $spanProcessors;

    /**
     * @var ResourceInfo
     */
    private $resource;

    /**
     * @var Sampler
     */
    private $sampler;

    public function __construct(?ResourceInfo $resource = null, ?Sampler $sampler = null, ?IdGenerator $idGenerator = null)
    {
        $this->spanProcessors = new SpanMultiProcessor();
        $this->resource = $resource ?? ResourceInfo::emptyResource();
        $this->sampler = $sampler ?? new ParentBased(new AlwaysOnSampler());
        $this->idGenerator = $idGenerator ?? new RandomIdGenerator();

        register_shutdown_function([$this, 'shutdown']);
    }

    public function shutdown(): void
    {
        $this->spanProcessors->shutdown();
    }

    public function getTracer(string $name, ?string $version = null): API\Tracer
    {
        $key = sprintf('%s@%s', $name, ($version ?? 'unknown'));

        if (isset($this->tracers[$key]) && $this->tracers[$key] instanceof API\Tracer) {
            return $this->tracers[$key];
        }

        $instrumentationLibrary = new InstrumentationLibrary($name, $version);

        return $this->tracers[$key] = new Tracer(
            $this,
            $instrumentationLibrary,
            ResourceInfo::merge($this->getResource(), ResourceInfo::defaultResource())
        );
    }

    public function addSpanProcessor(SpanProcessor $processor): self
    {
        $this->spanProcessors->addSpanProcessor($processor);

        return $this;
    }

    public function getSpanProcessor(): SpanMultiProcessor
    {
        return $this->spanProcessors;
    }

    public function getSampler(): Sampler
    {
        return $this->sampler;
    }

    public function getResource(): ResourceInfo
    {
        return clone $this->resource;
    }

    public function getIdGenerator(): IdGenerator
    {
        return $this->idGenerator;
    }
}
