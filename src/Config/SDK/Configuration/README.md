# Component providers

Each component in a configuration file, such as a sampler or a span exporter, is created by a
component provider. This follows the spec's
[SDK extension components](https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/sdk.md#sdk-extension-components).
A package can add its own components by shipping providers for them.

To parse a file, see the [package README](../README.md#usage).

## Write a component provider

A provider implements `OpenTelemetry\API\Configuration\Config\ComponentProvider`:

- `getConfig()` returns the node for the component. The node's name is the key used in the file.
- `createPlugin()` receives the processed properties and returns the component.

Nested components come from the registry passed to `getConfig()`:

- `component()` for a single component, such as `exporter`.
- `componentList()` for a list of components, such as `processors`.
- `componentMap()` for a map of components keyed by provider name.
- `componentNames()` for a list of provider names with no properties.

### Example: the batch span processor

This is the SDK's own `batch` provider. A name can have only one provider per component type, so
registering a second `batch` span processor fails at startup.

```php
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<SpanProcessorInterface>
 */
final class SpanProcessorBatch implements ComponentProvider
{
    /**
     * @param array{
     *     schedule_delay: int<0, max>,
     *     export_timeout: int<0, max>,
     *     max_queue_size: int<1, max>,
     *     max_export_batch_size: int<1, max>,
     *     exporter: ComponentPlugin<SpanExporterInterface>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SpanProcessorInterface
    {
        return new BatchSpanProcessor(
            exporter: $properties['exporter']->create($context),
            clock: Clock::getDefault(),
            maxQueueSize: $properties['max_queue_size'],
            scheduledDelayMillis: $properties['schedule_delay'],
            exportTimeoutMillis: $properties['export_timeout'],
            maxExportBatchSize: $properties['max_export_batch_size'],
        );
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('batch');
        $node
            ->children()
                ->integerNode('schedule_delay')->min(0)->defaultValue(5000)->end()
                ->integerNode('export_timeout')->min(0)->defaultValue(30000)->end()
                ->integerNode('max_queue_size')->min(1)->defaultValue(2048)->end()
                ->integerNode('max_export_batch_size')->min(1)->defaultValue(512)->end()
                ->append($registry->component('exporter', SpanExporterInterface::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
```

## Register a component provider

Providers are found with [`tbachert/spi`](https://github.com/Nevay/spi). List them in the package's
`composer.json` under the `ComponentProvider` interface:

```json
{
    "extra": {
        "spi": {
            "OpenTelemetry\\API\\Configuration\\Config\\ComponentProvider": [
                "Example\\SpanExporterExample"
            ]
        }
    }
}
```

The application must allow the plugin:

```shell
composer config allow-plugins.tbachert/spi true
```

Providers registered under the old `OpenTelemetry\Config\SDK\Configuration\ComponentProvider` key
still load, and log a deprecation warning.
