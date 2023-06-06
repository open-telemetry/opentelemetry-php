<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\Contrib\Otlp\ConsoleMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * This example uses the meter provider builder, then two different methods to achieve a
 * similar result: export metrics about the length of a job queue.
 *
 * First, an up/down meter is manually increased/decreased (eg as jobs are added/removed)
 * Second, an async callback is used to generate a point-in-time observation of the queue
 * length. The callback is executed when `reader::collect()` is called.
 */

$reader = new ExportingReader((new ConsoleMetricExporterFactory())->create());

$meterProvider = MeterProvider::builder()
    ->addReader($reader)
    ->build();

//example 1: manually adjust an up/down counter to track job queue length
$up_down = $meterProvider
    ->getMeter('demo')
    ->createUpDownCounter('queued', 'jobs', 'The number of jobs enqueued (non-async)');
$up_down->add(3); //jobs added
$up_down->add(-1); //job completed
$up_down->add(1); //job added

//example 2: observe the "queue", which happens every time `$reader->collect()` is called
$queue = [
    'job1',
    'job2',
    'job3',
];
$meterProvider
    ->getMeter('demo')
    ->createObservableGauge('queued', 'jobs', 'The number of jobs enqueued (async)')
    ->observe(static function (ObserverInterface $observer) use ($queue): void {
        $observer->observe(count($queue));
    });
$reader->collect();
array_pop($queue); //job completed
$reader->collect();

$meterProvider->shutdown();
