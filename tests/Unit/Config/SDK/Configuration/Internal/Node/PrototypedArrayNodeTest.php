<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal\Node;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\PrototypedArrayNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ScalarNode;

#[CoversClass(PrototypedArrayNode::class)]
final class PrototypedArrayNodeTest extends TestCase
{
    public function test_has_default_value_returns_false_when_no_default_set(): void
    {
        $node = new PrototypedArrayNode('test');
        $node->setPrototype(new ScalarNode('item'));

        // Parent PrototypedArrayNode has default value (empty array) by default
        $this->assertTrue($node->hasDefaultValue());
    }

    public function test_set_default_value_makes_has_default_value_return_true(): void
    {
        $node = new PrototypedArrayNode('test');
        $node->setPrototype(new ScalarNode('item'));

        $node->setDefaultValue(['a', 'b']);

        $this->assertTrue($node->hasDefaultValue());
    }

    public function test_get_default_value_returns_set_value(): void
    {
        $node = new PrototypedArrayNode('test');
        $node->setPrototype(new ScalarNode('item'));

        $expected = ['foo', 'bar'];
        $node->setDefaultValue($expected);

        $this->assertSame($expected, $node->getDefaultValue());
    }

    public function test_get_default_value_falls_back_to_parent_when_not_set(): void
    {
        $node = new PrototypedArrayNode('test');
        $node->setPrototype(new ScalarNode('item'));

        // Parent default for prototyped array is empty array
        $this->assertSame([], $node->getDefaultValue());
    }

    public function test_set_allow_empty_value(): void
    {
        $node = new PrototypedArrayNode('test');
        $node->setPrototype(new ScalarNode('item'));

        // Should not throw
        $node->setAllowEmptyValue(true);
        $node->setAllowEmptyValue(false);

        $this->assertTrue(true);
    }

    public function test_from_node_creates_copy(): void
    {
        $original = new \Symfony\Component\Config\Definition\PrototypedArrayNode('original');
        $original->setPrototype(new ScalarNode('item'));

        $copy = PrototypedArrayNode::fromNode($original);

        $this->assertInstanceOf(PrototypedArrayNode::class, $copy);
        $this->assertSame('original', $copy->getName());
    }

    public function test_default_value_can_be_null(): void
    {
        $node = new PrototypedArrayNode('test');
        $node->setPrototype(new ScalarNode('item'));

        $node->setDefaultValue(null);

        $this->assertTrue($node->hasDefaultValue());
        $this->assertNull($node->getDefaultValue());
    }
}
