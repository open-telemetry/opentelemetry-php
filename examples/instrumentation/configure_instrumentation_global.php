<?php

declare(strict_types=1);

namespace _;

use OpenTelemetry\Example\Example;
use const PHP_EOL;

/**
 * This example uses SPI (see root composer.json extra.spi) to configure an example auto-instrumentation from a YAML file.
 * The YAML file paths are relative to the current working directory.
 */
// EXAMPLE_INSTRUMENTATION_SPAN_NAME=test1234 php examples/instrumentation/configure_instrumentation_global.php
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_EXPERIMENTAL_CONFIG_FILE=examples/instrumentation/otel-sdk.yaml');
putenv('OTEL_PHP_INSTRUMENTATION_CONFIG_FILE=examples/instrumentation/otel-instrumentation.yaml');

require __DIR__ . '/../../vendor/autoload.php';

echo (new Example())->test(), PHP_EOL;
