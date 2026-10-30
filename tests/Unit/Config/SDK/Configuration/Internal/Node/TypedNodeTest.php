<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal\Node;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\BooleanNode;
use OpenTelemetry\Config\SDK\Configuration\Internal\Node\FloatNode;
use OpenTelemetry\Config\SDK\Configuration\Internal\Node\IntegerNode;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\BooleanNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\FloatNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\IntegerNodeDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

#[CoversClass(BooleanNode::class)]
#[CoversClass(IntegerNode::class)]
#[CoversClass(FloatNode::class)]
final class TypedNodeTest extends TestCase
{
    #[DataProvider('quotedLiteralProvider')]
    public function test_quoted_literal_is_coerced(NodeDefinition $definition, string $value, bool|int|float $expected): void
    {
        $node = $definition->getNode();

        $this->assertSame($expected, $node->finalize($node->normalize($value)));
    }

    public static function quotedLiteralProvider(): iterable
    {
        yield 'bool true' => [new BooleanNodeDefinition('key'), 'true', true];
        yield 'bool false' => [new BooleanNodeDefinition('key'), 'false', false];
        yield 'bool title case' => [new BooleanNodeDefinition('key'), 'True', true];
        yield 'bool upper case' => [new BooleanNodeDefinition('key'), 'FALSE', false];
        yield 'int' => [new IntegerNodeDefinition('key'), '5', 5];
        yield 'negative int' => [new IntegerNodeDefinition('key'), '-5', -5];
        yield 'hex int' => [new IntegerNodeDefinition('key'), '0x10', 16];
        yield 'float' => [new FloatNodeDefinition('key'), '1.5', 1.5];
        yield 'int as float' => [new FloatNodeDefinition('key'), '5', 5.0];
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/data-model.md#yaml-file-format
     */
    #[DataProvider('invalidLiteralProvider')]
    public function test_invalid_quoted_literal_is_rejected(NodeDefinition $definition, string $value): void
    {
        $node = $definition->getNode();

        $this->expectException(InvalidTypeException::class);
        $node->normalize($value);
    }

    public static function invalidLiteralProvider(): iterable
    {
        yield 'bool word' => [new BooleanNodeDefinition('key'), 'enabled'];
        yield 'bool empty' => [new BooleanNodeDefinition('key'), ''];
        yield 'bool blank' => [new BooleanNodeDefinition('key'), ' '];
        yield 'bool mixed case' => [new BooleanNodeDefinition('key'), 'tRUE'];
        yield 'bool yaml 1.1 on' => [new BooleanNodeDefinition('key'), 'on'];
        yield 'bool yaml 1.1 no' => [new BooleanNodeDefinition('key'), 'no'];
        yield 'bool one' => [new BooleanNodeDefinition('key'), '1'];
        yield 'bool zero' => [new BooleanNodeDefinition('key'), '0'];
        yield 'int word' => [new IntegerNodeDefinition('key'), 'five'];
        yield 'int decimal' => [new IntegerNodeDefinition('key'), '5.5'];
        yield 'int empty' => [new IntegerNodeDefinition('key'), ''];
        yield 'float word' => [new FloatNodeDefinition('key'), 'one'];
        yield 'float empty' => [new FloatNodeDefinition('key'), ''];
    }
}
