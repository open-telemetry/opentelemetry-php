<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;

/**
 * @psalm-suppress InternalMethod
 */
$reader = new ExportingReader(new MetricExporter((new StreamTransportFactory())->create(STDOUT, 'application/x-ndjson'), /*Temporality::CUMULATIVE*/));

$meterProvider = MeterProvider::builder()
    ->addReader($reader)
    ->build();

$histogram = $meterProvider->getMeter('io.opentelemetry.contrib.php')->createHistogram('demo');

$histogram->record(50);
$histogram->record(7);
$reader->collect();

$meterProvider->shutdown();
