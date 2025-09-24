<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelector;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AggregationTemporalitySelector::class)]
/**
 * @see https://github.com/open-telemetry/opentelemetry-java/blob/v1.54.0/sdk/metrics/src/test/java/io/opentelemetry/sdk/metrics/export/AggregationTemporalitySelectorTest.java
 */
class AggregationTemporalitySelectorTest extends TestCase
{
    public function test_always_cumulative()
    {
        $selector = AggregationTemporalitySelector::alwaysCumulative();

        $counter = $this->createMock(MetricMetadataInterface::class);
        $counter->method('instrumentType')->willReturn(InstrumentType::COUNTER);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($counter));

        $observableCounter = $this->createMock(MetricMetadataInterface::class);
        $observableCounter->method('instrumentType')->willReturn(InstrumentType::ASYNCHRONOUS_COUNTER);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($observableCounter));

        $histogram = $this->createMock(MetricMetadataInterface::class);
        $histogram->method('instrumentType')->willReturn(InstrumentType::HISTOGRAM);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($histogram));

        $observableGauge = $this->createMock(MetricMetadataInterface::class);
        $observableGauge->method('instrumentType')->willReturn(InstrumentType::ASYNCHRONOUS_GAUGE);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($observableGauge));

        $upDownCounter = $this->createMock(MetricMetadataInterface::class);
        $upDownCounter->method('instrumentType')->willReturn(InstrumentType::UP_DOWN_COUNTER);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($upDownCounter));

        $observableUpDownCounter = $this->createMock(MetricMetadataInterface::class);
        $observableUpDownCounter->method('instrumentType')->willReturn(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($observableUpDownCounter));

        $gauge = $this->createMock(MetricMetadataInterface::class);
        $gauge->method('instrumentType')->willReturn(InstrumentType::GAUGE);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($gauge));
    }

    public function test_delta_preferred()
    {
        $selector = AggregationTemporalitySelector::deltaPreferred();

        $counter = $this->createMock(MetricMetadataInterface::class);
        $counter->method('instrumentType')->willReturn(InstrumentType::COUNTER);
        $this->assertSame(Temporality::DELTA, $selector->temporality($counter));

        $observableCounter = $this->createMock(MetricMetadataInterface::class);
        $observableCounter->method('instrumentType')->willReturn(InstrumentType::ASYNCHRONOUS_COUNTER);
        $this->assertSame(Temporality::DELTA, $selector->temporality($observableCounter));

        $histogram = $this->createMock(MetricMetadataInterface::class);
        $histogram->method('instrumentType')->willReturn(InstrumentType::HISTOGRAM);
        $this->assertSame(Temporality::DELTA, $selector->temporality($histogram));

        $observableGauge = $this->createMock(MetricMetadataInterface::class);
        $observableGauge->method('instrumentType')->willReturn(InstrumentType::ASYNCHRONOUS_GAUGE);
        $this->assertSame(Temporality::DELTA, $selector->temporality($observableGauge));

        $upDownCounter = $this->createMock(MetricMetadataInterface::class);
        $upDownCounter->method('instrumentType')->willReturn(InstrumentType::UP_DOWN_COUNTER);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($upDownCounter));

        $observableUpDownCounter = $this->createMock(MetricMetadataInterface::class);
        $observableUpDownCounter->method('instrumentType')->willReturn(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($observableUpDownCounter));

        $gauge = $this->createMock(MetricMetadataInterface::class);
        $gauge->method('instrumentType')->willReturn(InstrumentType::GAUGE);
        $this->assertSame(Temporality::DELTA, $selector->temporality($gauge));
    }

    public function test_low_memory()
    {
        $selector = AggregationTemporalitySelector::lowMemory();

        $counter = $this->createMock(MetricMetadataInterface::class);
        $counter->method('instrumentType')->willReturn(InstrumentType::COUNTER);
        $this->assertSame(Temporality::DELTA, $selector->temporality($counter));

        $observableCounter = $this->createMock(MetricMetadataInterface::class);
        $observableCounter->method('instrumentType')->willReturn(InstrumentType::ASYNCHRONOUS_COUNTER);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($observableCounter));

        $histogram = $this->createMock(MetricMetadataInterface::class);
        $histogram->method('instrumentType')->willReturn(InstrumentType::HISTOGRAM);
        $this->assertSame(Temporality::DELTA, $selector->temporality($histogram));

        $observableGauge = $this->createMock(MetricMetadataInterface::class);
        $observableGauge->method('instrumentType')->willReturn(InstrumentType::ASYNCHRONOUS_GAUGE);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($observableGauge));

        $upDownCounter = $this->createMock(MetricMetadataInterface::class);
        $upDownCounter->method('instrumentType')->willReturn(InstrumentType::UP_DOWN_COUNTER);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($upDownCounter));

        $observableUpDownCounter = $this->createMock(MetricMetadataInterface::class);
        $observableUpDownCounter->method('instrumentType')->willReturn(InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER);
        $this->assertSame(Temporality::CUMULATIVE, $selector->temporality($observableUpDownCounter));

        $gauge = $this->createMock(MetricMetadataInterface::class);
        $gauge->method('instrumentType')->willReturn(InstrumentType::GAUGE);
        $this->assertSame(Temporality::DELTA, $selector->temporality($gauge));
    }
}
