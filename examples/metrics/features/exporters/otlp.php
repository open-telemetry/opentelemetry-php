<?php

declare(strict_types=1);

require __DIR__ . '/../../../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\Contrib\OtlpHttp\MetricExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

$clock = ClockFactory::getDefault();
$reader = new ExportingReader(MetricExporter::create(
    new Client(),
    new HttpFactory(),
    new HttpFactory(),
    'http://collector:4318/v1/metrics',
), $clock);

$meterProvider = new MeterProvider(
    null,
    ResourceInfoFactory::defaultResource(),
    $clock,
    Attributes::factory(),
    new InstrumentationScopeFactory(Attributes::factory()),
    [$reader],
    new CriteriaViewRegistry(),
    new WithSampledTraceExemplarFilter(),
    new ImmediateStalenessHandlerFactory(),
);

$meter = $meterProvider->getMeter('io.opentelemetry.contrib.php');
$meter
    ->createObservableUpDownCounter('process.memory.usage', 'By', 'The amount of physical memory in use.')
    ->observe(static function (ObserverInterface $observer): void {
        $observer->observe(memory_get_usage(true));
    });

$serverDuration = $meter
    ->createHistogram('http.server.duration', 'ms', 'measures the duration inbound HTTP requests');

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
