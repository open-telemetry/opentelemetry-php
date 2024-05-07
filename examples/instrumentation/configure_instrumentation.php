<?php

declare(strict_types=1);

namespace _;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ExtensionHookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\Config\SDK\Configuration;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Example\Example;
use const PHP_EOL;

/**
 * This example uses SPI (see root composer.json extra.spi) to configure an example auto-instrumentation from a YAML file
 */
// EXAMPLE_INSTRUMENTATION_SPAN_NAME=test1234 php examples/instrumentation/configure_instrumentation.php

require __DIR__ . '/../../vendor/autoload.php';

Configuration::parseFile(__DIR__ . '/otel-sdk.yaml')->create(new Context())->setAutoShutdown(true)->buildAndRegisterGlobal();
$configuration = \OpenTelemetry\Config\SDK\Instrumentation::parseFile(__DIR__ . '/otel-instrumentation.yaml')->create();

$hookManager = new ExtensionHookManager();
$storage = \OpenTelemetry\Context\Context::storage();

foreach (ServiceLoader::load(Instrumentation::class) as $instrumentation) {
    $instrumentation->register($hookManager, $configuration, $storage);
}

$scope = $storage->attach($hookManager->enable($storage->current()));

try {
    echo (new Example())->test(), PHP_EOL;
} finally {
    $scope->detach();
}