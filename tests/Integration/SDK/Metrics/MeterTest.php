<?php

declare(strict_types=1);

namespace Integration\SDK\Metrics;

use OpenTelemetry\API\Metrics\ObserverInterface;
use Opentelemetry\Proto\Metrics\V1\AggregationTemporality;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Export\InMemoryStorageManager;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class MeterTest extends TestCase
{
    use TestState;

    public function tearDown(): void
    {
        InMemoryStorageManager::metrics()->exchangeArray([]);
        parent::tearDown();
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.48.0/specification/metrics/sdk_exporters/otlp.md#additional-environment-variable-configuration
     */
    #[DataProvider('temporality_provider')]
    public function test_temporality_preference(string $preference, array $expected): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE', $preference);
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_PROTOCOL', 'memory/json');
        $meterProvider = (new MeterProviderFactory())->create(ResourceInfoFactory::emptyResource());
        $meter = $meterProvider->getMeter('test');
        $counter = $meter->createCounter('sync.counter');
        $counter->add(1);
        $upDownCounter = $meter->createUpDownCounter('sync.upDownCounter');
        $upDownCounter->add(1);
        $histogram = $meter->createHistogram('sync.histogram');
        $histogram->record(2);
        $gauge = $meter->createGauge('sync.gauge');
        $gauge->record(1);
        $meter->createObservableCounter('async.observableCounter', callbacks: fn (ObserverInterface $observer) => $observer->observe(1));
        $meter->createObservableUpDownCounter('async.observableUpDownCounter', callbacks: fn (ObserverInterface $observer) => $observer->observe(1));
        $meter->createObservableGauge('async.observableGauge', callbacks: fn (ObserverInterface $observer) => $observer->observe(1));
        $meterProvider->forceFlush();

        $storage = InMemoryStorageManager::metrics();
        $this->assertCount(1, $storage);
        $metrics = $storage[0];
        $data = json_decode($metrics);
        $this->assertCount(7, $data->resourceMetrics[0]->scopeMetrics[0]->metrics);
        foreach ($data->resourceMetrics[0]->scopeMetrics[0]->metrics as $metric) {
            if (isset($metric->sum)) {
                $this->assertSame($this->mapEnumToValue($expected[$metric->name]), $metric->sum->aggregationTemporality, $metric->name);
            }
            if (isset($metric->histogram)) {
                $this->assertSame($this->mapEnumToValue($expected[$metric->name]), $metric->histogram->aggregationTemporality, $metric->name);
            }
            if (isset($metric->gauge)) {
                $this->assertObjectNotHasProperty('aggregationTemporality', $metric->gauge, $metric->name);
            }
        }
    }

    public static function temporality_provider(): array
    {
        return [
            'Cumulative' => [KnownValues::VALUE_TEMPORALITY_CUMULATIVE, [
                'sync.counter' => Temporality::CUMULATIVE,
                'sync.upDownCounter' => Temporality::CUMULATIVE,
                'sync.histogram' => Temporality::CUMULATIVE,
                'async.observableCounter' => Temporality::CUMULATIVE,
                'async.observableUpDownCounter' => Temporality::CUMULATIVE,
            ]],
            'Delta' => [KnownValues::VALUE_TEMPORALITY_DELTA, [
                'sync.counter' => Temporality::DELTA,
                'sync.upDownCounter' => Temporality::CUMULATIVE,
                'sync.histogram' => Temporality::DELTA,
                'async.observableCounter' => Temporality::DELTA,
                'async.observableUpDownCounter' => Temporality::CUMULATIVE,
            ]],
            'LowMemory' => ['LowMemory', [
                'sync.counter' => Temporality::DELTA,
                'sync.upDownCounter' => Temporality::CUMULATIVE,
                'sync.histogram' => Temporality::DELTA,
                'async.observableCounter' => Temporality::CUMULATIVE,
                'async.observableUpDownCounter' => Temporality::CUMULATIVE,
            ]],
        ];
    }

    public function test_gauge_temporality_not_set(): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE', 'delta');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_PROTOCOL', 'memory/json');
        $meterProvider = (new MeterProviderFactory())->create(ResourceInfoFactory::emptyResource());
        $meter = $meterProvider->getMeter('test');
        $gauge = $meter->createGauge('async.gauge');
        $gauge->record(5);
        $meter->createObservableGauge('async.observableGauge', callbacks: fn (ObserverInterface $observer) => $observer->observe(6));
        $meterProvider->forceFlush();

        $storage = InMemoryStorageManager::metrics();
        $this->assertCount(1, $storage);
        $metrics = $storage[0];
        $data = json_decode($metrics);
        $this->assertCount(2, $data->resourceMetrics[0]->scopeMetrics[0]->metrics);
        foreach ($data->resourceMetrics[0]->scopeMetrics[0]->metrics as $metric) {
            $this->assertObjectNotHasProperty('aggregationTemporality', $metric->gauge->dataPoints[0], $metric->name);
        }
    }

    private function mapEnumToValue(Temporality $temporality): int
    {
        return match ($temporality) {
            Temporality::CUMULATIVE => AggregationTemporality::AGGREGATION_TEMPORALITY_CUMULATIVE,
            Temporality::DELTA => AggregationTemporality::AGGREGATION_TEMPORALITY_DELTA,
        };
    }
}
