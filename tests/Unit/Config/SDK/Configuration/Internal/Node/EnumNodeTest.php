final <?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal\Node;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\EnumNode;
use PHPUnit\Framework\TestCase;

class EnumNodeTest extends TestCase
{
    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\Node\EnumNode
     */
    public function test_enum_node_extends_symfony_enum_node(): void
    {
        $this->assertTrue(is_subclass_of(EnumNode::class, \Symfony\Component\Config\Definition\EnumNode::class));
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\Node\NodeTrait
     */
    public function test_enum_node_uses_node_trait(): void
    {
        $enumNode = new \ReflectionClass(EnumNode::class);
        $traits = $enumNode->getTraits();

        $traitNames = array_map(fn ($trait) => $trait->getName(), $traits);
        $this->assertContains('OpenTelemetry\Config\SDK\Configuration\Internal\Node\NodeTrait', $traitNames);
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\Node\EnumNode
     */
    public function test_enum_node_has_correct_namespace(): void
    {
        $reflection = new \ReflectionClass(EnumNode::class);

        $this->assertEquals(
            'OpenTelemetry\Config\SDK\Configuration\Internal\Node',
            $reflection->getNamespaceName()
        );
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\Node\EnumNode
     */
    public function test_enum_node_is_not_abstract(): void
    {
        $reflection = new \ReflectionClass(EnumNode::class);

        $this->assertFalse($reflection->isAbstract());
    }

    /**
     * @covers \OpenTelemetry\Config\SDK\Configuration\Internal\Node\EnumNode
     */
    public function test_enum_node_inherits_from_symfony_enum_node(): void
    {
        $this->assertTrue(is_subclass_of(EnumNode::class, \Symfony\Component\Config\Definition\NodeInterface::class));
    }
}
