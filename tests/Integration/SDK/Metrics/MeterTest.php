<?php

declare(strict_types=1);

namespace Integration\SDK\Metrics;

use Opentelemetry\Proto\Metrics\V1\AggregationTemporality;
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

    #[DataProvider('temporality_preference_provider')]
    public function test_temporality_preference(string $preference, Temporality $expected): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE', $preference);
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_PROTOCOL', 'memory/json');
        $meterProvider = (new MeterProviderFactory())->create(ResourceInfoFactory::emptyResource());
        $meter = $meterProvider->getMeter('test');
        $counter = $meter->createCounter('test.counter');
        $counter->add(1);
        $upDownCounter = $meter->createUpDownCounter('test.upDownCounter');
        $upDownCounter->add(1);
        $histogram = $meter->createHistogram('test.histogram');
        $histogram->record(2);
        $meter->createObservableCounter('test.observableCounter', callbacks: fn () => 1);
        $meter->createObservableUpDownCounter('test.observableUpDownCounter', callbacks: fn () => 1);
        $meterProvider->forceFlush();

        $storage = InMemoryStorageManager::metrics();
        $this->assertCount(1, $storage);
        $metrics = $storage[0];
        $data = json_decode($metrics);
        $this->assertCount(5, $data->resourceMetrics[0]->scopeMetrics[0]->metrics);
        foreach ($data->resourceMetrics[0]->scopeMetrics[0]->metrics as $metric) {
            if (isset($metric->sum)) {
                $this->assertSame($this->mapEnumToValue($expected), $metric->sum->aggregationTemporality);
            }
            if (isset($metric->histogram)) {
                $this->assertSame($this->mapEnumToValue($expected), $metric->histogram->aggregationTemporality);
            }
        }
    }

    public static function temporality_preference_provider(): array
    {
        return [
            ['cumulative', Temporality::CUMULATIVE],
            ['delta', Temporality::DELTA],
        ];
    }

    /**
     * "This configuration uses Delta aggregation temporality for Synchronous Counter and Histogram and
     *  uses Cumulative aggregation temporality for Synchronous UpDownCounter, Asynchronous Counter,
     *  and Asynchronous UpDownCounter instrument kinds."
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.48.0/specification/metrics/sdk_exporters/otlp.md#additional-environment-variable-configuration
     */
    public function test_low_memory_temporality(): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE', 'LowMemory');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_PROTOCOL', 'memory/json');
        $meterProvider = (new MeterProviderFactory())->create(ResourceInfoFactory::emptyResource());
        $meter = $meterProvider->getMeter('test');
        $counter = $meter->createCounter('sync.counter'); //delta
        $counter->add(1);
        $upDownCounter = $meter->createUpDownCounter('sync.upDownCounter'); //cumulative
        $upDownCounter->add(1);
        $histogram = $meter->createHistogram('sync.histogram'); //delta
        $histogram->record(2);
        $meter->createObservableCounter('async.observableCounter', callbacks: fn () => 1); //cumulative
        $meter->createObservableUpDownCounter('async.observableUpDownCounter', callbacks: fn () => 1); //cumulative
        $meterProvider->forceFlush();

        $storage = InMemoryStorageManager::metrics();
        $this->assertCount(1, $storage);
        $metrics = $storage[0];
        $data = json_decode($metrics);
        $this->assertCount(5, $data->resourceMetrics[0]->scopeMetrics[0]->metrics);
        foreach ($data->resourceMetrics[0]->scopeMetrics[0]->metrics as $metric) {
            switch ($metric->name) {
                case 'sync.counter':
                    $this->assertSame($this->mapEnumToValue(Temporality::DELTA), $metric->sum->aggregationTemporality);

                    break;
                case 'sync.histogram':
                    $this->assertSame($this->mapEnumToValue(Temporality::DELTA), $metric->histogram->aggregationTemporality);

                    break;
                case 'sync.upDownCounter':
                case 'async.observableCounter':
                case 'async.observableUpDownCounter':
                    $this->assertSame($this->mapEnumToValue(Temporality::CUMULATIVE), $metric->sum->aggregationTemporality);

                    break;
                default:
                    $this->fail('Unexpected metric name ' . $metric->name);
            }

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
