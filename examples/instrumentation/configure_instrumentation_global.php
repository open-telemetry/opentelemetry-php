<?php

declare(strict_types=1);

namespace _;

use OpenTelemetry\Example\Example;
use const PHP_EOL;

/**
 * This example uses SPI (see root composer.json extra.spi) to configure an example auto-instrumentation from a YAML file
 */
// EXAMPLE_INSTRUMENTATION_SPAN_NAME=test1234 php examples/instrumentation/configure_instrumentation.php
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv(sprintf('OTEL_EXPERIMENTAL_CONFIG_FILE=%s/%s', __DIR__, 'otel-sdk.yaml'));
putenv(sprintf('OTEL_PHP_INSTRUMENTATION_CONFIG_FILE=%s/%s', __DIR__, 'otel-instrumentation.yaml'));

require __DIR__ . '/../../vendor/autoload.php';

echo (new Example())->test(), PHP_EOL;
