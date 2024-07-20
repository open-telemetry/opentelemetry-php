<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Name;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use OpenTelemetry\SDK\Metrics\MeterConfig;
use OpenTelemetry\SDK\Metrics\MeterConfigurator;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeterConfigurator::class)]
#[CoversClass(MeterConfig::class)]
class MeterConfigTest extends TestCase
{
    public function test_disable_scopes(): void
    {
        $meterProvider = MeterProvider::builder()
            ->addReader(new ExportingReader(new InMemoryExporter()))
            ->setConfigurator(
                Configurator::builder()
                    ->addCondition(new Name('~two~'), State::DISABLED)
                    ->build()
            )
            ->build();

        $this->assertInstanceOf(MeterProvider::class, $meterProvider);

        $meter_one = $meterProvider->getMeter('one');
        $meter_two = $meterProvider->getMeter('two');
        $meter_three = $meterProvider->getMeter('three');

        $instruments = [];
        $instruments[] = $meter_two->createCounter('a');
        //        $instruments[] = $meter_two->createObservableCounter('b');
        $instruments[] = $meter_two->createUpDownCounter('c');
        //        $instruments[] = $meter_two->createObservableUpDownCounter('d');
        $instruments[] = $meter_two->createHistogram('e');
        $instruments[] = $meter_two->createGauge('f');
        //        $instruments[] = $meter_two->createObservableGauge('g');

        foreach ($instruments as $id => $instrument) {
            $this->assertFalse($instrument->enabled(), sprintf('instrument %s is enabled', $id));
        }

        $this->assertTrue($meter_one->isEnabled());
        $this->assertFalse($meter_two->isEnabled());
        $this->assertTrue($meter_three->isEnabled());

        $meterProvider->updateConfigurator(new Configurator());

        $this->assertTrue($meter_two->isEnabled());

        foreach ($instruments as $instrument) {
            $this->assertTrue($instrument->enabled());
        }
    }
}
