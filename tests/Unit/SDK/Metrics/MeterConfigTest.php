<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Metrics\Noop\NoopCounter;
use OpenTelemetry\API\Metrics\Noop\NoopGauge;
use OpenTelemetry\API\Metrics\Noop\NoopHistogram;
use OpenTelemetry\API\Metrics\Noop\NoopObservableCounter;
use OpenTelemetry\API\Metrics\Noop\NoopObservableGauge;
use OpenTelemetry\API\Metrics\Noop\NoopObservableUpDownCounter;
use OpenTelemetry\API\Metrics\Noop\NoopUpDownCounter;
use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Name;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use OpenTelemetry\SDK\Metrics\MeterConfig;
use OpenTelemetry\SDK\Metrics\MeterConfigurator;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeterConfigurator::class)]
#[CoversClass(MeterConfig::class)]
class MeterConfigTest extends TestCase
{
    public function test_disable_scopes(): void
    {
        $meterProvider = MeterProvider::builder()
            ->addMeterConfiguratorCondition(new Condition(new Name('~two~'), State::DISABLED)) //meter two disabled
            ->build();

        $meter_one = $meterProvider->getMeter('one');
        $meter_two = $meterProvider->getMeter('two');
        $meter_three = $meterProvider->getMeter('three');

        $this->assertTrue($meter_one->isEnabled());
        $this->assertFalse($meter_two->isEnabled());
        $this->assertTrue($meter_three->isEnabled());

        $this->assertInstanceOf(NoopCounter::class, $meter_two->createCounter('a'));
        $this->assertInstanceOf(NoopObservableCounter::class, $meter_two->createObservableCounter('b'));
        $this->assertInstanceOf(NoopUpDownCounter::class, $meter_two->createUpDownCounter('c'));
        $this->assertInstanceOf(NoopObservableUpDownCounter::class, $meter_two->createObservableUpDownCounter('d'));
        $this->assertInstanceOf(NoopHistogram::class, $meter_two->createHistogram('e'));
        $this->assertInstanceOf(NoopGauge::class, $meter_two->createGauge('f'));
        $this->assertInstanceOf(NoopObservableGauge::class, $meter_two->createObservableGauge('g'));
    }
}
