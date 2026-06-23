<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\ArrayNode;
use OpenTelemetry\Config\SDK\Configuration\Internal\Node\PrototypedArrayNode;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\ArrayNodeDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayNodeDefinition::class)]
final class ArrayNodeDefinitionTest extends TestCase
{
    public function test_create_node_returns_array_node(): void
    {
        $definition = new ArrayNodeDefinition('test');
        $definition->addDefaultsIfNotSet();

        $node = $definition->getNode(true);

        $this->assertInstanceOf(ArrayNode::class, $node);
    }

    public function test_create_node_returns_prototyped_array_node_for_prototype(): void
    {
        $definition = new ArrayNodeDefinition('test');
        $definition->prototype('scalar');

        $node = $definition->getNode(true);

        $this->assertInstanceOf(PrototypedArrayNode::class, $node);
    }

    public function test_default_value_is_applied_to_node(): void
    {
        $definition = new ArrayNodeDefinition('test');
        $definition->addDefaultsIfNotSet();
        $definition->defaultValue(['key' => 'value']);

        $node = $definition->getNode(true);

        $this->assertTrue($node->hasDefaultValue());
        $this->assertSame(['key' => 'value'], $node->getDefaultValue());
    }

    public function test_default_value_returns_static(): void
    {
        $definition = new ArrayNodeDefinition('test');

        $result = $definition->defaultValue(['a' => 'b']);

        $this->assertSame($definition, $result);
    }

    public function test_cannot_be_empty(): void
    {
        $definition = new ArrayNodeDefinition('test');

        $result = $definition->cannotBeEmpty();

        $this->assertSame($definition, $result);
    }
}
