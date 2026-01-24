<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\Configuration;

use InvalidArgumentException;
use OpenTelemetry\Config\SDK\Configuration\Environment\ArrayEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceReader;
use OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\ArrayNodeDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * @see https://opentelemetry.io/docs/specs/otel/configuration/data-model/#environment-variable-substitution
 */
#[CoversClass(EnvSubstitutionNormalization::class)]
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

        $node = new ArrayNodeDefinition('');
        $node->children()->scalarNode('key');
        (new EnvSubstitutionNormalization($envReader))->apply($node);

        $result = (new Processor())->process($node->getNode(true), [[
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

        $node = new ArrayNodeDefinition('');
        $node->children()->scalarNode('key');
        (new EnvSubstitutionNormalization($envReader))->apply($node);

        $this->expectException(InvalidArgumentException::class);

        (new Processor())->process($node->getNode(true), [[
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

    public static function invalidSubstitutionDataProvider(): iterable
    {
        yield ['${STRING_VALUE:?error}'];

        yield ['${file:test}'];
        yield ['${0abc}'];
    }
}
