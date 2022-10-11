<?php

// Example based on https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/supplementary-guidelines.md#synchronous-example
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria\InstrumentNameCriteria;
use OpenTelemetry\SDK\Metrics\View\ViewTemplate;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

$clock = ClockFactory::getDefault();
$reader = new ExportingReader(new MetricExporter(STDOUT, /*Temporality::CUMULATIVE*/), $clock);

// Let's imagine we export the metrics as Histogram, and to simplify the story we will only have one histogram bucket (-Inf, +Inf):
$views = new CriteriaViewRegistry();
$views->register(
    new InstrumentNameCriteria('http.server.duration'),
    ViewTemplate::create()
        ->withAttributeKeys(['http.method', 'http.status_code'])
        ->withAggregation(new ExplicitBucketHistogramAggregation([])),
);

$meterProvider = new MeterProvider(
    null,
    ResourceInfoFactory::emptyResource(),
    $clock,
    Attributes::factory(),
    new InstrumentationScopeFactory(Attributes::factory()),
    [$reader],
    $views,
    new WithSampledTraceExemplarFilter(),
    new ImmediateStalenessHandlerFactory(),
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

$meterProvider->shutdown();
