<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use OpenTelemetry\Sdk\Metrics\Counter;
use OpenTelemetry\Sdk\Metrics\Exporters\PrometheusExporter;
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

$counter = new Counter('opentelemetry_prometheus_counter', 'Just a quick measurement');

$counter->increment();

$exporter = new PrometheusExporter(CollectorRegistry::getDefault());

$exporter->export([$counter]);
