<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal;

use LogicException;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry as ComponentProviderRegistryInterface;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Internal\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

#[CoversClass(ComponentProviderRegistry::class)]
final class ComponentProviderRegistryTest extends TestCase
{
    public function test_constructor(): void
    {
        $registry = new ComponentProviderRegistry([], new NodeBuilder());
        $this->assertInstanceOf(ComponentProviderRegistry::class, $registry);
    }

    public function test_register_provider(): void
    {
        $registry = new ComponentProviderRegistry([], new NodeBuilder());

        $provider = new class implements ComponentProvider {
            public function createPlugin(array $properties, Context $context): string
            {
                return 'test';
            }

            public function getConfig(ComponentProviderRegistryInterface $registry, NodeBuilder $builder): ArrayNodeDefinition
            {
                return $builder->arrayNode('test_provider');
            }
        };

        $registry->register($provider);
        $this->assertTrue(true);
    }

    public function test_register_duplicate_provider_throws(): void
    {
        $registry = new ComponentProviderRegistry([], new NodeBuilder());

        $provider = new class implements ComponentProvider {
            public function createPlugin(array $properties, Context $context): string
            {
                return 'test';
            }

            public function getConfig(ComponentProviderRegistryInterface $registry, NodeBuilder $builder): ArrayNodeDefinition
            {
                return $builder->arrayNode('duplicate_provider');
            }
        };

        $registry->register($provider);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Duplicate component provider');
        $registry->register($provider);
    }

    public function test_component_returns_node_definition(): void
    {
        $registry = new ComponentProviderRegistry([], new NodeBuilder());

        $node = $registry->component('my_component', 'SomeType');
        $this->assertInstanceOf(NodeDefinition::class, $node);
    }

    public function test_component_list_returns_array_node_definition(): void
    {
        $registry = new ComponentProviderRegistry([], new NodeBuilder());

        $node = $registry->componentList('my_list', 'SomeType');
        $this->assertInstanceOf(ArrayNodeDefinition::class, $node);
    }

    public function test_component_map_returns_array_node_definition(): void
    {
        $registry = new ComponentProviderRegistry([], new NodeBuilder());

        $node = $registry->componentMap('my_map', 'SomeType');
        $this->assertInstanceOf(ArrayNodeDefinition::class, $node);
    }

    public function test_component_names_returns_array_node_definition(): void
    {
        $registry = new ComponentProviderRegistry([], new NodeBuilder());

        $node = $registry->componentNames('my_names', 'SomeType');
        $this->assertInstanceOf(ArrayNodeDefinition::class, $node);
    }

    public function test_track_resources(): void
    {
        $registry = new ComponentProviderRegistry([], new NodeBuilder());
        $resources = $this->createMock(ResourceCollection::class);

        $registry->trackResources($resources);
        $this->assertTrue(true);
    }

    public function test_track_resources_null(): void
    {
        $registry = new ComponentProviderRegistry([], new NodeBuilder());

        $registry->trackResources(null);
        $this->assertTrue(true);
    }
}
