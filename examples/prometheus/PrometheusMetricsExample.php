<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../src/SDK/Metrics/compatibility.php';

use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Clock;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;
use OpenTelemetry\SDK\Metrics\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Exemplar;
use OpenTelemetry\SDK\Metrics\MetricWriter\StreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use OpenTelemetry\SDK\Trace\SystemClock;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

Redis::setDefaultOptions(
    [
        'host' => 'redis',
        'port' => 6379,
        'password' => null,
        'timeout' => 0.1, // in seconds
        'read_timeout' => '10', // in seconds
        'persistent_connections' => false,
    ]
);

$counterStream = new SynchronousMetricStream(
    new AttributeProcessor\Identity(),
    new Aggregation\Sum(true),
    new Exemplar\NoopReservoir(),
    SystemClock::getInstance()->now(),
);
$counter = new StreamWriter($counterStream->writable(), new AttributesFactory(), new Clock(SystemClock::getInstance()));
$prometheusReader = $counterStream->register(Temporality::Delta);

$counter->record(1);


$labels = [];
$prometheusCounter = CollectorRegistry::getDefault()->getOrRegisterCounter(
    '',
    'opentelemetry_prometheus_counter',
    'Just a quick measurement',
    $labels,
);

/** @var Data\Sum $data */
$data = $counterStream->collect($prometheusReader, SystemClock::getInstance()->now());
foreach ($data->dataPoints as $dataPoint) {
    $prometheusCounter->incBy($dataPoint->value, array_map($dataPoint->attributes->get(...), $labels));
}
