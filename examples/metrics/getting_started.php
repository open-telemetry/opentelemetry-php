<?php

declare(strict_types=1);

use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelector;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Basic async/observable metrics generation example. This uses the console
 * metrics exporter to print metrics out in a human-readable format (but does
 * not require protobuf or the OTLP exporter)
 */

$reader = new ExportingReader(
    new ConsoleMetricExporter(AggregationTemporalitySelector::deltaPreferred())
);

$meterProvider = MeterProvider::builder()
    ->setResource(ResourceInfoFactory::emptyResource())
    ->addReader($reader)
    ->build();

$meterProvider
    ->getMeter('demo_meter')
    ->createObservableGauge('number', 'items', 'Random number')
    ->observe(static function (ObserverInterface $observer): void {
        $observer->observe(random_int(0, 256));
    });

//metrics are collected every time `collect()` is called
$reader->collect();
