<?php

declare(stfinal rict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Metrics;

use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MeterConfig;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class MeterConfigTest extends TestCase
{
    const T0 = 0;
    const T1 = 1;
    const T2 = 2;

    public function test_disable_scopes(): void
    {
        $meterProvider = MeterProvider::builder()
            ->addReader(new ExportingReader(new InMemoryExporter()))
            ->setConfigurator(
                Configurator::meter()
                 ->with(static fn (MeterConfig $config) => $config->setDisabled(true), name: 'two')
            )
            ->build();

        $this->assertInstanceOf(MeterProvider::class, $meterProvider);

        $meter_one = $meterProvider->getMeter('one');
        $meter_two = $meterProvider->getMeter('two');
        $meter_three = $meterProvider->getMeter('three');

        $instruments = [];
        $instruments[] = $meter_two->createCounter('a');
        $instruments[] = $meter_two->createObservableCounter('b');
        $instruments[] = $meter_two->createUpDownCounter('c');
        $instruments[] = $meter_two->createObservableUpDownCounter('d');
        $instruments[] = $meter_two->createHistogram('e');
        $instruments[] = $meter_two->createGauge('f');
        $instruments[] = $meter_two->createObservableGauge('g');

        foreach ($instruments as $id => $instrument) {
            $this->assertFalse($instrument->isEnabled(), sprintf('instrument %s is disabled', $id));
        }

        $this->assertTrue($meter_one->createCounter('test')->isEnabled());
        $this->assertFalse($meter_two->createCounter('test')->isEnabled());
        $this->assertTrue($meter_three->createCounter('test')->isEnabled());

        $meterProvider->updateConfigurator(Configurator::meter());

        $this->assertTrue($meter_two->createCounter('test')->isEnabled());

        foreach ($instruments as $instrument) {
            $this->assertTrue($instrument->isEnabled());
        }
    }

    /**
     * If a Meter is disabled, it MUST behave equivalently to No-op Meter
     */
    #[Group('metrics-compliance')]
    public function test_metrics_not_exported_when_disabled(): void
    {
        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);
        $meterProvider = MeterProvider::builder()
            ->addReader($reader)
            ->setConfigurator(
                Configurator::meter()
                    ->with(static fn (MeterConfig $config) => $config->setDisabled(true), name: '*')
            )
            ->build();
        $meter = $meterProvider->getMeter('test');
        $this->assertFalse($meter->createCounter('test')->isEnabled());
        $counter = $meter->createCounter('a');
        $async_counter = $meter->createObservableCounter('b', callbacks: function (ObserverInterface $o) {
            $this->fail('observer from disabled meter should not have been called');
        });
        $this->assertFalse($counter->isEnabled());
        $this->assertFalse($async_counter->isEnabled());
        $counter->add(1);
        $reader->collect();
        $metrics = $exporter->collect(true);
        $this->assertSame([], $metrics);
    }

    /**
     * If a meter is disabled, its streams should be dropped. Any previously collected
     * data will be lost. If a disabled meter is re-enabled, the streams should be
     * recreated.
     */
    public function test_streams_recreated_on_enable(): void
    {
        $clock = new TestClock(self::T0);
        $disabledConfigurator = Configurator::meter()
            ->with(static fn (MeterConfig $config) => $config->setDisabled(true), name: '*');
        $exporter = new InMemoryExporter(temporality: Temporality::CUMULATIVE);
        $reader = new ExportingReader($exporter);
        $meterProvider = MeterProvider::builder()
            ->addReader($reader)
            ->setClock($clock)
            ->build();

        $c = $meterProvider->getMeter('test')->createCounter('c');

        //t0, meter is enabled
        $c->add(1);

        //t1, disable meter
        $clock->setTime(self::T1);
        $meterProvider->updateConfigurator($disabledConfigurator);
        $c->add(10);

        //t2, {sum=100, startTimestamp=t2}; must not export {sum=101, startTimestamp=t0}
        $clock->setTime(self::T2);
        $meterProvider->updateConfigurator(Configurator::meter());
        $c->add(100);

        $reader->collect();
        $metrics = $exporter->collect();
        $this->assertCount(1, $metrics);
        $metric = $metrics[0];

        $this->assertInstanceOf(Sum::class, $metric->data);
        $this->assertIsArray($metric->data->dataPoints);
        $this->assertCount(1, $metric->data->dataPoints);
        $dataPoint = $metric->data->dataPoints[0];

        $this->assertSame(self::T2, $dataPoint->startTimestamp);
        $this->assertSame(100, $dataPoint->value);
    }
}
