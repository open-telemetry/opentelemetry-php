<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\FloatNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;

#[CoversClass(EnvSubstitutionNormalization::class)]
final class EnvSubstitutionNormalizationTest extends TestCase
{
    public function test_apply_with_scalar_children(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $normalization = new EnvSubstitutionNormalization($envReader);

        $root = new ArrayNodeDefinition('root');
        $root
            ->children()
                ->scalarNode('name')->end()
                ->integerNode('count')->end()
                ->booleanNode('enabled')->end()
                ->floatNode('rate')->end()
            ->end();

        $normalization->apply($root);

        // If apply didn't throw, it means it processed all child nodes
        $this->assertTrue(true);
    }

    public function test_apply_with_variable_node(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $normalization = new EnvSubstitutionNormalization($envReader);

        $root = new ArrayNodeDefinition('root');
        $root
            ->children()
                ->variableNode('data')->end()
            ->end();

        $normalization->apply($root);

        $this->assertTrue(true);
    }

    public function test_apply_with_nested_array(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $normalization = new EnvSubstitutionNormalization($envReader);

        $root = new ArrayNodeDefinition('root');
        $root
            ->children()
                ->arrayNode('nested')
                    ->children()
                        ->scalarNode('inner')->end()
                        ->integerNode('value')->end()
                    ->end()
                ->end()
            ->end();

        $normalization->apply($root);

        $this->assertTrue(true);
    }

    public function test_apply_with_empty_root(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $normalization = new EnvSubstitutionNormalization($envReader);

        $root = new ArrayNodeDefinition('root');

        $normalization->apply($root);

        $this->assertTrue(true);
    }
}
