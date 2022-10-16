<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;

class TracerProviderBuilder
{
    // @var array<SpanProcessorInterface>
    private ?array $spanProcessors = [];
    private ?ResourceInfo $resource = null;
    private ?SamplerInterface $sampler = null;
    private bool $autoShutdown = false;

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

    /**
     * Automatically shut down the tracer provider on process completion. If not set, the user is responsible for calling `shutdown`.
     */
    public function setAutoShutdown(bool $shutdown): self
    {
        $this->autoShutdown = $shutdown;

        return $this;
    }

    public function setSampler(SamplerInterface $sampler): self
    {
        $this->sampler = $sampler;

        return $this;
    }

    public function build(): TracerProviderInterface
    {
        $tracerProvider = new TracerProvider(
            $this->spanProcessors,
            $this->sampler ?? new ParentBased(new AlwaysOnSampler()),
            $this->resource ?? ResourceInfoFactory::defaultResource(),
        );
        if ($this->autoShutdown) {
            ShutdownHandler::register(fn (?CancellationInterface $cancellation = null): bool => $tracerProvider->shutdown($cancellation));
        }

        return $tracerProvider;
    }
}
