<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\Configuration\Internal;

use InvalidArgumentException;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Internal\Substitution;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Substitution::class)]
final class SubstitutionTest extends TestCase
{
    public function test_process_returns_string_without_dollar_sign_unchanged(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->expects($this->never())->method('read');

        $this->assertSame('hello world', Substitution::process('hello world', $envReader));
    }

    public function test_process_substitutes_env_variable(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->with('MY_VAR')->willReturn('my_value');

        $this->assertSame('my_value', Substitution::process('${MY_VAR}', $envReader));
    }

    public function test_process_returns_empty_string_for_undefined_variable(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->willReturn(null);

        $this->assertSame('', Substitution::process('${UNDEFINED}', $envReader));
    }

    public function test_process_uses_default_value_when_variable_undefined(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->with('UNDEFINED')->willReturn(null);

        $this->assertSame('fallback', Substitution::process('${UNDEFINED:-fallback}', $envReader));
    }

    public function test_process_uses_env_value_over_default(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->with('DEFINED')->willReturn('actual');

        $this->assertSame('actual', Substitution::process('${DEFINED:-fallback}', $envReader));
    }

    public function test_process_handles_escape_sequence(): void
    {
        $envReader = $this->createMock(EnvReader::class);

        $this->assertSame('a $ b', Substitution::process('a $$ b', $envReader));
    }

    public function test_process_handles_multiple_substitutions(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->willReturnMap([
            ['A', 'hello'],
            ['B', 'world'],
        ]);

        $this->assertSame('hello world', Substitution::process('${A} ${B}', $envReader));
    }

    public function test_process_handles_env_prefix(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->with('MY_VAR')->willReturn('value');

        $this->assertSame('value', Substitution::process('${env:MY_VAR}', $envReader));
    }

    public function test_process_throws_on_empty_variable_name(): void
    {
        $envReader = $this->createMock(EnvReader::class);

        $this->expectException(InvalidArgumentException::class);

        Substitution::process('${}', $envReader);
    }

    public function test_process_throws_on_invalid_variable_name(): void
    {
        $envReader = $this->createMock(EnvReader::class);

        $this->expectException(InvalidArgumentException::class);

        Substitution::process('${0abc}', $envReader);
    }

    public function test_process_throws_on_invalid_substitution_syntax(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->willReturn('value');

        $this->expectException(InvalidArgumentException::class);

        Substitution::process('${MY_VAR:?error}', $envReader);
    }

    public function test_process_with_text_around_substitution(): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->with('NAME')->willReturn('world');

        $this->assertSame('hello world!', Substitution::process('hello ${NAME}!', $envReader));
    }

    public function test_process_dollar_sign_without_brace_is_literal(): void
    {
        $envReader = $this->createMock(EnvReader::class);

        $this->assertSame('a $ b', Substitution::process('a $ b', $envReader));
    }

    #[DataProvider('escapeSequenceDataProvider')]
    public function test_escape_sequences(string $input, string $expected): void
    {
        $envReader = $this->createMock(EnvReader::class);
        $envReader->method('read')->willReturnMap([
            ['VAR', 'value'],
        ]);

        $this->assertSame($expected, Substitution::process($input, $envReader));
    }

    public static function escapeSequenceDataProvider(): iterable
    {
        yield 'double dollar escapes' => ['$${VAR}', '${VAR}'];
        yield 'triple dollar' => ['$$${VAR}', '$value'];
        yield 'quadruple dollar' => ['$$$${VAR}', '$${VAR}'];
    }
}
