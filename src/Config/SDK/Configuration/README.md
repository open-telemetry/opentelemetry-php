# Configuration

Allows creation of an extensible configuration model according to the
[OTel file configuration specification](https://opentelemetry.io/docs/specs/otel/configuration/file-configuration/).

## Installation

```shell
composer require tbachert/otel-sdk-configuration
```

Additionally, you will need either `ext-yaml` or `symfony/yaml` to load configuration from yaml config files.

## Usage

### Parsing config

```php
$factory = new ConfigurationFactory(
    [
        // all available providers ...
    ],
    new OpenTelemetryConfiguration(),
    new EnvSourceReader([
        new ArrayEnvSource($_SERVER),
        new PhpIniEnvSource(),
    ]),
);
$configuration = $factory->parseFile(__DIR__ . '/config.yaml');
$openTelemetry = $configuration->create(new Context());
```

### Defining component providers

See [examples](./examples) for component provider examples.

###### Example: BatchSpanProcessor

```php
final class SpanProcessorBatch implements ComponentProvider {

    /**
     * @param array{
     *     schedule_delay: int<0, max>,
     *     export_timeout: int<0, max>,
     *     max_queue_size: int<0, max>,
     *     max_export_batch_size: int<0, max>,
     *     exporter: ComponentPlugin<SpanExporter>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SpanProcessor {
        // ...
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition {
        $node = new ArrayNodeDefinition('batch');
        $node
            ->children()
                ->integerNode('schedule_delay')->min(0)->defaultValue(5000)->end()
                ->integerNode('export_timeout')->min(0)->defaultValue(30000)->end()
                ->integerNode('max_queue_size')->min(0)->defaultValue(2048)->end()
                ->integerNode('max_export_batch_size')->min(0)->defaultValue(512)->end()
                ->append($registry->component('exporter', SpanExporter::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
```
