<?php

declare(strict_types=1);

namespace Benchmark;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Name;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;

class MetricBench
{
    private MeterInterface $enabled;
    private MeterInterface $disabled;
    private MetricReaderInterface $reader;
    public function __construct()
    {
        $exporter = new NoopMetricExporter();
        $this->reader = new ExportingReader($exporter);
        $meterProvider = MeterProvider::builder()
            ->addReader($this->reader)
            ->setConfigurator(
                Configurator::builder()
                    ->addCondition(new Name('~disabled~'), State::DISABLED)
                    ->build()
            )
            ->build();
        $this->enabled = $meterProvider->getMeter('enabled');
        $this->disabled = $meterProvider->getMeter('disabled');
    }

    /**
     * @Revs({100, 1000})
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     * @ParamProviders("provideMeasurementCounts")
     */
    public function bench_sync_measurements(array $params): void
    {
        $meter = $params['enabled'] === false ? $this->disabled : $this->enabled;
        $counter = $meter->createCounter('a');
        for ($i=0; $i < $params['count']; $i++) {
            $counter->add(1);
        }
    }

    /**
     * @Revs({100, 1000})
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     * @ParamProviders("provideMeasurementCounts")
     * @Groups("async")
     */
    public function bench_async_measurements(array $params): void
    {
        $meter = $params['enabled'] === false ? $this->disabled : $this->enabled;
        for ($i=0; $i < $params['count']; $i++) {
            $meter->createObservableCounter('b', callbacks: function (ObserverInterface $o) {
                $o->observe(1);
            });
        }
        $this->reader->collect();
    }

    public function provideMeasurementCounts(): \Generator
    {
        yield 'disabled+10' => ['enabled' => false, 'count' => 10];
        yield 'disabled+100' => ['enabled' => false, 'count' => 100];
        yield 'disabled+1000' => ['enabled' => false, 'count' => 1000];
        yield 'enabled+10' => ['enabled' => true, 'count' => 10];
        yield 'enabled+100' => ['enabled' => true, 'count' => 100];
    }
}
