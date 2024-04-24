<?php declare(strict_types=1);
namespace _;

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ExtensionHookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\NoopHookManager;
use OpenTelemetry\Config\SDK\Configuration;
use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\ConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\PhpIniEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource;
use OpenTelemetry\Example\Example;
use OpenTelemetry\Example\ExampleConfigProvider;
use OpenTelemetry\Example\ExampleInstrumentation;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use const PHP_EOL;

// EXAMPLE_INSTRUMENTATION_SPAN_NAME=test1234 php examples/instrumentation/configure_instrumentation.php

require __DIR__ . '/../../vendor/autoload.php';

ServiceLoader::register(HookManager::class, ExtensionHookManager::class);
ServiceLoader::register(ComponentProvider::class, ExampleConfigProvider::class);
ServiceLoader::register(Instrumentation::class, ExampleInstrumentation::class);

$sdk = Configuration::parseFile(__DIR__ . '/otel-sdk.yaml')->create(new Context())->setAutoShutdown(true)->build();
$configuration = parseInstrumentationConfig(__DIR__ . '/otel-instrumentation.yaml')->create(new Context());

$hookManager = hookManager();
$context = new Context($sdk->getTracerProvider());
$storage = \OpenTelemetry\Context\Context::storage();

foreach (ServiceLoader::load(Instrumentation::class) as $instrumentation) {
    $instrumentation->register($hookManager, $context, $configuration, $storage);
}

$scope = $storage->attach($hookManager->enable($storage->current()));

try {
    echo (new Example())->test(), PHP_EOL;
} finally {
    $scope->detach();
}


function hookManager(): HookManager {
    foreach (ServiceLoader::load(HookManager::class) as $hookManager) {
        return $hookManager;
    }

    return new NoopHookManager();
}

function parseInstrumentationConfig(string $file): ComponentPlugin {
    // TODO Include in SDK config?
    return (new ConfigurationFactory(
        ServiceLoader::load(ComponentProvider::class),
        new class implements ComponentProvider {

            /**
             * @param array{
             *     config: list<ComponentPlugin<InstrumentationConfiguration>>,
             * } $properties
             */
            public function createPlugin(array $properties, Context $context): ConfigurationRegistry {
                $configurationRegistry = new ConfigurationRegistry();
                foreach ($properties['config'] as $configuration) {
                    $configurationRegistry->add($configuration->create($context));
                }

                return $configurationRegistry;
            }

            public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition {
                $root = new ArrayNodeDefinition('instrumentation');
                $root
                    ->children()
                        // TODO add disabled_instrumentations arrayNode to allow disabling specific instrumentation classes?
                        ->append($registry->componentList('config', InstrumentationConfiguration::class))
                    ->end()
                ;

                return $root;
            }
        },
        new EnvSourceReader([
            new ServerEnvSource(),
            new PhpIniEnvSource(),
        ]),
    ))->parseFile($file);
}
