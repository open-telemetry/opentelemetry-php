<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal\Node;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\ArrayNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayNode::class)]
final class ArrayNodeTest extends TestCase
{
    public function test_has_default_value_returns_false_by_default(): void
    {
        $node = new ArrayNode('test');

        // Parent ArrayNode does not have a default value unless addDefaultsIfNotSet is used
        $this->assertFalse($node->hasDefaultValue());
    }

    public function test_set_default_value_makes_has_default_value_return_true(): void
    {
        $node = new ArrayNode('test');

        $node->setDefaultValue(['key' => 'value']);

        $this->assertTrue($node->hasDefaultValue());
    }

    public function test_get_default_value_returns_set_value(): void
    {
        $node = new ArrayNode('test');

        $expected = ['key' => 'value'];
        $node->setDefaultValue($expected);

        $this->assertSame($expected, $node->getDefaultValue());
    }

    public function test_set_allow_empty_value(): void
    {
        $node = new ArrayNode('test');

        // Should not throw
        $node->setAllowEmptyValue(true);
        $node->setAllowEmptyValue(false);

        $this->assertTrue(true);
    }

    public function test_from_node_creates_copy(): void
    {
        $original = new \Symfony\Component\Config\Definition\ArrayNode('original');

        $copy = ArrayNode::fromNode($original);

        $this->assertInstanceOf(ArrayNode::class, $copy);
        $this->assertSame('original', $copy->getName());
    }

    public function test_default_value_can_be_null(): void
    {
        $node = new ArrayNode('test');

        $node->setDefaultValue(null);

        $this->assertTrue($node->hasDefaultValue());
        $this->assertNull($node->getDefaultValue());
    }

    public function test_finalize_value_returns_null_for_null(): void
    {
        $node = new ArrayNode('test');
        $node->setAllowEmptyValue(true);

        $this->assertNull($node->finalizeValue(null));
    }
}
