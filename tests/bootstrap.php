<?php

declare(strict_types=1);

use DG\BypassFinals;

require_once __DIR__ . '/../vendor/autoload.php';

BypassFinals::enable();

// Load Registry files to register default factories for tests
$registryFiles = [
    __DIR__ . '/../src/SDK/Trace/SpanExporter/_register.php',
    __DIR__ . '/../src/SDK/Metrics/MetricExporter/_register.php',
    __DIR__ . '/../src/SDK/Logs/Exporter/_register.php',
    __DIR__ . '/../src/SDK/Propagation/_register.php',
    __DIR__ . '/../src/Contrib/Otlp/_register.php',
    __DIR__ . '/../src/Contrib/Grpc/_register.php',
    __DIR__ . '/../src/Contrib/Zipkin/_register.php',
    __DIR__ . '/../src/Extension/Propagator/B3/_register.php',
    __DIR__ . '/../src/Extension/Propagator/Jaeger/_register.php',
    __DIR__ . '/../src/Extension/Propagator/CloudTrace/_register.php',
];

foreach ($registryFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}
