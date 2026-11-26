<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Behavior\Internal\ConfiguredLogger;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressionStrategy;
use Psr\Log\LoggerInterface;

class TracerProviderBuilder
{
    /** @var list<SpanProcessorInterface> */
    private ?array $spanProcessors = [];
    private ?ResourceInfo $resource = null;
    private ?SamplerInterface $sampler = null;
    private ?Configurator $configurator = null;
    private ?SpanSuppressionStrategy $spanSuppressionStrategy = null;
    private ?MeterProviderInterface $meterProvider = null;
    private ?LoggerInterface $logger = null;

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

    public function setSpanSuppressionStrategy(SpanSuppressionStrategy $spanSuppressionStrategy): self
    {
        $this->spanSuppressionStrategy = $spanSuppressionStrategy;

        return $this;
    }

    public function setMeterProvider(MeterProviderInterface $meterProvider): self
    {
        $this->meterProvider = $meterProvider;

        return $this;
    }

    /**
     * Set the logger for the provider's internal messages. Without one, they go to the global internal
     * logger, filtered by its global level. Span processors are built before they are added, so give them
     * their logger when you build them, for example with {@see BatchSpanProcessorBuilder::setLogger()}.
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function build(): TracerProviderInterface
    {
        return new TracerProvider(
            $this->spanProcessors,
            $this->sampler,
            $this->resource,
            configurator: $this->configurator ?? Configurator::tracer(),
            spanSuppressionStrategy: $this->spanSuppressionStrategy ?? new NoopSuppressionStrategy(),
            meterProvider: $this->meterProvider,
            logger: $this->logger ?? new ConfiguredLogger(),
        );
    }
}
