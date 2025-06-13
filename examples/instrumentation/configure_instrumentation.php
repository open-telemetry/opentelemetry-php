<?php

declare(strict_types=1);

namespace _;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ExtensionHookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\Config\SDK\Configuration;
use OpenTelemetry\Example\Example;
use const PHP_EOL;

/**
 * This example uses SPI (see root composer.json extra.spi) to configure an example auto-instrumentation from a YAML file
 */
// EXAMPLE_INSTRUMENTATION_SPAN_NAME=test1234 php examples/instrumentation/configure_instrumentation.php

require __DIR__ . '/../../vendor/autoload.php';

Configuration::parseFile(__DIR__ . '/otel-sdk.yaml')->create(new Context())->setAutoShutdown(true)->buildAndRegisterGlobal();
$configuration = \OpenTelemetry\Config\SDK\Instrumentation::parseFile(__DIR__ . '/otel-sdk.yaml')->create();
$hookManager = new ExtensionHookManager();
$context = new \OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context(Globals::tracerProvider(), Globals::meterProvider(), Globals::loggerProvider(), Globals::propagator());

foreach (ServiceLoader::load(Instrumentation::class) as $instrumentation) {
    $instrumentation->register($hookManager, $configuration, $context);
}

echo (new Example())->test(), PHP_EOL;
