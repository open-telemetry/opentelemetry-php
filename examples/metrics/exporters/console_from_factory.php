<?php

declare(strict_types=1);

use OpenTelemetry\SDK\Metrics\MeterProviderFactory;

require __DIR__ . '/../../../vendor/autoload.php';

/**
 * Create a console exporter from environment variables, and generate a histogram.
 */

putenv('OTEL_METRICS_EXPORTER=console');

$meterProvider = (new MeterProviderFactory())->create();

$meter = $meterProvider->getMeter('io.opentelemetry.contrib.php');
$hist = $meter ->createHistogram('example', 'bytes', 'The number of bytes received.');
for ($i=0; $i<=5000; $i++) {
    $hist->record(rand(0, 1024));
}

$meterProvider->shutdown();
