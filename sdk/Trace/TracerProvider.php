<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Sdk\Resource\ResourceConstants;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SpanMultiProcessor;
use OpenTelemetry\Trace as API;

final class TracerProvider implements API\TracerProvider
{

    /**
     * @var Tracer[]
     */
    protected $tracers;

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

    public function __construct(?ResourceInfo $resource = null, ?Sampler $sampler = null)
    {
        $this->spanProcessors = new SpanMultiProcessor();
        $this->resource = $resource ?? ResourceInfo::emptyResource();
        if (null == $sampler) {
            $this->sampler = new AlwaysOnSampler();
        } else {
            $this->sampler = $sampler;
        }
        register_shutdown_function([$this, 'shutdown']);
    }

    public function shutdown(): void
    {
        $this->spanProcessors->shutdown();
    }

    public function getTracer(string $name, ?string $version = ''): API\Tracer
    {
        if (isset($this->tracers[$name]) && $this->tracers[$name] instanceof API\Tracer) {
            return $this->tracers[$name];
        }

        $spanContext = SpanContext::generateSampled();

        $resource = ResourceInfo::create(
            new Attributes(
                [
                    ResourceConstants::TELEMETRY_SDK_NAME => 'opentelemetry',
                    ResourceConstants::TELEMETRY_SDK_LANGUAGE => 'php',
                    ResourceConstants::TELEMETRY_SDK_VERSION => 'dev',
                    ResourceConstants::SERVICE_NAME => $name,
                    ResourceConstants::SERVICE_VERSION => $version,
                    ResourceConstants::SERVICE_INSTANCE_ID => uniqid($name . $version),
                ]
            )
        );

        return $this->tracers[$name] = new Tracer(
            $this,
            ResourceInfo::merge($this->getResource(), $resource),
            $spanContext
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
        return $this->resource;
    }
}
