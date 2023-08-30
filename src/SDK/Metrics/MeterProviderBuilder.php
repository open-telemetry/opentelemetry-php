<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

class MeterProviderBuilder
{
    // @var array<MetricReaderInterface>
    private array $metricReaders = [];
    private ?ResourceInfo $resource = null;
    private ?ExemplarFilterInterface $exemplarFilter = null;

    public function setResource(ResourceInfo $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function setExemplarFilter(ExemplarFilterInterface $exemplarFilter): self
    {
        $this->exemplarFilter = $exemplarFilter;

        return $this;
    }

    public function addReader(MetricReaderInterface $reader): self
    {
        $this->metricReaders[] = $reader;

        return $this;
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function build(): MeterProviderInterface
    {
        return new MeterProvider(
            null,
            $this->resource ?? ResourceInfoFactory::emptyResource(),
            ClockFactory::getDefault(),
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            $this->metricReaders,
            new CriteriaViewRegistry(),
            $this->exemplarFilter ?? new WithSampledTraceExemplarFilter(),
            new NoopStalenessHandlerFactory(),
        );
    }
}
