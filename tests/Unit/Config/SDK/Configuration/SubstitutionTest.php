<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration;

use function assert;
use InvalidArgumentException;
use OpenTelemetry\Config\SDK\Configuration\Environment\ArrayEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\ArrayNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\BooleanNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NormalizationsAware;
use OpenTelemetry\Config\SDK\Configuration\Internal\Substitution;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @see https://opentelemetry.io/docs/specs/otel/configuration/data-model/#environment-variable-substitution
 */
#[CoversClass(EnvSubstitutionNormalization::class)]
#[CoversClass(Substitution::class)]
final class SubstitutionTest extends TestCase
{
    #[DataProvider('substitutionDataProvider')]
    public function test_substitution(string $value, string $expected): void
    {
        $envReader = new EnvSourceReader([new ArrayEnvSource([
            'STRING_VALUE' => 'value',
            'BOOL_VALUE' => 'true',
            'INT_VALUE' => '1',
            'FLOAT_VALUE' => '1.1',
            'HEX_VALUE' => '0xdeadbeef',
            'INVALID_MAP_VALUE' => "value\nkey:value",
            'DO_NOT_REPLACE_ME' => 'Never use this value',
            'REPLACE_ME' => '${DO_NOT_REPLACE_ME}',
            'VALUE_WITH_ESCAPE' => 'value$$',
        ])]);

        $result = (new Processor())->process(self::node($envReader), [[
            'key' => $value,
        ]]);

        $this->assertSame($expected, $result['key'] ?? '');
    }

    #[DataProvider('invalidSubstitutionDataProvider')]
    public function test_invalid_substitution(string $value): void
    {
        $envReader = new EnvSourceReader([new ArrayEnvSource([
            'STRING_VALUE' => 'value',
            'BOOL_VALUE' => 'true',
            'INT_VALUE' => '1',
            'FLOAT_VALUE' => '1.1',
            'HEX_VALUE' => '0xdeadbeef',
            'INVALID_MAP_VALUE' => "value\nkey:value",
            'DO_NOT_REPLACE_ME' => 'Never use this value',
            'REPLACE_ME' => '${DO_NOT_REPLACE_ME}',
            'VALUE_WITH_ESCAPE' => 'value$$',
        ])]);

        $this->expectException(InvalidArgumentException::class);

        (new Processor())->process(self::node($envReader), [[
            'key' => $value,
        ]]);
    }

    public static function substitutionDataProvider(): iterable
    {
        yield ['${STRING_VALUE}', 'value'];
        yield ['${BOOL_VALUE}', 'true'];
        yield ['${INT_VALUE}', '1'];
        yield ['${FLOAT_VALUE}', '1.1'];
        yield ['${HEX_VALUE}', '0xdeadbeef'];
        yield ['"${STRING_VALUE}"', '"value"'];
        yield ['"${BOOL_VALUE}"', '"true"'];
        yield ['"${INT_VALUE}"', '"1"'];
        yield ['"${FLOAT_VALUE}"', '"1.1"'];
        yield ['"${HEX_VALUE}"', '"0xdeadbeef"'];
        yield ['${env:STRING_VALUE}', 'value'];
        yield ['${INVALID_MAP_VALUE}', "value\nkey:value"];
        yield ['foo ${STRING_VALUE} ${FLOAT_VALUE}', 'foo value 1.1'];
        yield ['${UNDEFINED_KEY}', ''];
        yield ['${UNDEFINED_KEY:-fallback}', 'fallback'];
        yield ['${REPLACE_ME}', '${DO_NOT_REPLACE_ME}'];
        yield ['${UNDEFINED_KEY:-${STRING_VALUE}}', '${STRING_VALUE}'];
        yield ['$${STRING_VALUE}', '${STRING_VALUE}'];
        yield ['$$${STRING_VALUE}', '$value'];
        yield ['$$$${STRING_VALUE}', '$${STRING_VALUE}'];
        yield ['$${STRING_VALUE:-fallback}', '${STRING_VALUE:-fallback}'];
        yield ['$${STRING_VALUE:-${STRING_VALUE}}', '${STRING_VALUE:-value}'];
        yield ['${UNDEFINED_KEY:-$${UNDEFINED_KEY}}', '${UNDEFINED_KEY:-${UNDEFINED_KEY}}'];
        yield ['${VALUE_WITH_ESCAPE}', 'value$$'];
        yield ['a $$ b', 'a $ b'];
        yield ['a $ b', 'a $ b'];

        yield ['${env:-test}', 'test'];
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/data-model.md#environment-variable-substitution
     */
    #[DataProvider('verbatimValueDataProvider')]
    public function test_value_is_substituted_verbatim(string $value, ?string $expected): void
    {
        $envReader = new EnvSourceReader([new ArrayEnvSource([
            'EMPTY' => '',
            'BLANK' => '  ',
            'TAB' => "\t",
            'PADDED' => ' value ',
        ])]);

        $result = (new Processor())->process(self::node($envReader), [[
            'key' => $value,
        ]]);

        $this->assertSame($expected, $result['key'] ?? null);
    }

    /**
     * A blank result resolves to null, as `key:   ` would after substitution.
     */
    public static function verbatimValueDataProvider(): iterable
    {
        yield 'empty uses default' => ['${EMPTY:-fallback}', 'fallback'];
        yield 'blank' => ['${BLANK}', null];
        yield 'blank does not use default' => ['${BLANK:-fallback}', null];
        yield 'tab' => ['${TAB}', null];
        yield 'blank in text' => ['a${BLANK}b', 'a  b'];
        yield 'padded' => ['${PADDED}', ' value '];
        yield 'padded in text' => ['a${PADDED}b', 'a value b'];
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/data-model.md#yaml-file-format
     */
    #[DataProvider('booleanSubstitutionDataProvider')]
    public function test_boolean_substitution(string $env, ?bool $expected): void
    {
        $envReader = new EnvSourceReader([new ArrayEnvSource(['BOOL_VALUE' => $env])]);
        $node = new ArrayNodeDefinition('');
        $node->append(new BooleanNodeDefinition('key'));
        $node = $node->getNode(true);
        assert($node instanceof NormalizationsAware);
        $node->setNormalizations([new EnvSubstitutionNormalization($envReader)]);

        if ($expected === null) {
            $this->expectException(InvalidTypeException::class);
        }
        $result = (new Processor())->process($node, [['key' => '${BOOL_VALUE}']]);

        $this->assertSame($expected, $result['key']);
    }

    public static function booleanSubstitutionDataProvider(): iterable
    {
        yield 'true' => ['true', true];
        yield 'title case' => ['True', true];
        yield 'upper case' => ['FALSE', false];
        yield 'mixed case' => ['tRUE', null];
        yield 'yaml 1.1 on' => ['on', null];
        yield 'yaml 1.1 yes' => ['yes', null];
        yield 'one' => ['1', null];
        yield 'zero' => ['0', null];
    }

    public static function invalidSubstitutionDataProvider(): iterable
    {
        yield ['${STRING_VALUE:?error}'];

        yield ['${file:test}'];
        yield ['${0abc}'];
    }

    private static function node(EnvReader $envReader): NodeInterface
    {
        $node = new ArrayNodeDefinition('');
        $node->children()->scalarNode('key');

        $node = $node->getNode(true);
        assert($node instanceof NormalizationsAware);
        $node->setNormalizations([new EnvSubstitutionNormalization($envReader)]);

        return $node;
    }
}
