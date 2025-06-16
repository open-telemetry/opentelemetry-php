<?php

declare(strict_types=1);

namespace _;

use OpenTelemetry\Example\Example;
use const PHP_EOL;
use function putenv;

/**
 * This example uses SPI (see root composer.json extra.spi) to configure an example auto-instrumentation from environment variables.
 */
// php examples/instrumentation/configure_instrumentation_env.php
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_PHP_EXAMPLE_INSTRUMENTATION_SPAN_NAME=example span');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_METRICS_EXPORTER=none');
putenv('OTEL_LOGS_EXPORTER=none');

require __DIR__ . '/../../vendor/autoload.php';

echo (new Example())->test(), PHP_EOL;
