<?php

declare(strict_types=1);

use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

require 'vendor/autoload.php';

/**
 * Example of using weakly referenced observer callbacks. Binds the lifetime of
 * a callback to its bound object, the returned `ObserverCallbackInterface` is
 * ignored to automatically detach the callback once the original object is
 * garbage collected.
 */

$reader = new ExportingReader(
    new ConsoleMetricExporter(Temporality::DELTA)
);

$meterProvider = MeterProvider::builder()
    ->setResource(ResourceInfoFactory::emptyResource())
    ->addReader($reader)
    ->build();

$callback = new class() {
    public function __invoke(ObserverInterface $observer)
    {
        $observer->observe(random_int(1, 10));
    }
};

$meterProvider
    ->getMeter('demo_meter')
    ->createObservableGauge('number', 'items', 'Random number')
    ->observe($callback); //weak-ref to callback

$reader->collect(); //metrics (data-points) collected (callback invoked)
unset($callback);
$reader->collect(); //no metrics (data-points) collected, because the callback was garbage-collected due to weak-ref
