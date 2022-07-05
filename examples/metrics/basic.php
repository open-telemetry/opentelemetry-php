<?php

// Example based on https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/supplementary-guidelines.md#synchronous-example
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogram;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporter;
use OpenTelemetry\SDK\Metrics\MetricMetadata;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\SdkMeterProvider;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentNameCriteria;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

final class VarDumpingExporter implements MetricExporter
{
    /**
     * @var string|Temporality|null
     */
    private $temporality;

    /**
     * @param string|Temporality|null $temporality
     */
    public function __construct($temporality = null)
    {
        $this->temporality = $temporality;
    }

    public function temporality(MetricMetadata $metric)
    {
        return $this->temporality ?? $metric->temporality();
    }

    public function export(iterable $batch): bool
    {
        var_dump($batch);

        return true;
    }

    public function shutdown(): bool
    {
        return true;
    }

    public function forceFlush(): bool
    {
        return true;
    }
}

$clock = ClockFactory::getDefault();
$reader = new ExportingReader(new VarDumpingExporter(/*Temporality::CUMULATIVE*/), $clock);
$meterProvider = new SdkMeterProvider(
    null,
    ResourceInfoFactory::emptyResource(),
    $clock,
    new InstrumentationScopeFactory(Attributes::factory()),
    $reader,
    Attributes::factory(),
    new ImmediateStalenessHandlerFactory(),
);

// Let's imagine we export the metrics as Histogram, and to simplify the story we will only have one histogram bucket (-Inf, +Inf):
$meterProvider->registerView(
    new InstrumentNameCriteria('http.server.duration'),
    null,
    null,
    ['http.method', 'http.status_code'],
    fn () => new ExplicitBucketHistogram([]),
);

$serverDuration = $meterProvider->getMeter('io.opentelemetry.contrib.php')->createHistogram(
    'http.server.duration',
    'ms',
    'measures the duration inbound HTTP requests',
);

// During the time range (T0, T1]:
$serverDuration->record(50, ['http.method' => 'GET', 'http.status_code' => 200]);
$serverDuration->record(100, ['http.method' => 'GET', 'http.status_code' => 200]);
$serverDuration->record(1, ['http.method' => 'GET', 'http.status_code' => 500]);
$reader->collect();

// During the time range (T1, T2]:
$reader->collect();

// During the time range (T2, T3]:
$serverDuration->record(5, ['http.method' => 'GET', 'http.status_code' => 500]);
$serverDuration->record(2, ['http.method' => 'GET', 'http.status_code' => 500]);
$reader->collect();

// During the time range (T3, T4]:
$serverDuration->record(100, ['http.method' => 'GET', 'http.status_code' => 200]);
$reader->collect();

// During the time range (T4, T5]:
$serverDuration->record(100, ['http.method' => 'GET', 'http.status_code' => 200]);
$serverDuration->record(30, ['http.method' => 'GET', 'http.status_code' => 200]);
$serverDuration->record(50, ['http.method' => 'GET', 'http.status_code' => 200]);
$reader->collect();
