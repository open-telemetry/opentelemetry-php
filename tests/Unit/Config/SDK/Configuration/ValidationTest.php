<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration;

use InvalidArgumentException;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validation::class)]
final class ValidationTest extends TestCase
{
    #[DataProvider('numberProvider')]
    public function test_ensure_number(mixed $value, int|float|null $expected): void
    {
        $this->assertSame($expected, Validation::ensureNumber()($value));
    }

    public static function numberProvider(): iterable
    {
        yield 'null' => [null, null];
        yield 'int' => [1, 1];
        yield 'float' => [1.5, 1.5];
        yield 'numeric int string' => ['8080', 8080];
        yield 'numeric float string' => ['1.5', 1.5];
    }

    public function test_ensure_number_rejects_non_numeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validation::ensureNumber()('one');
    }

    public function test_ensure_regex_pattern_accepts_null(): void
    {
        $this->assertNull(Validation::ensureRegexPattern()(null));
    }

    public function test_ensure_regex_pattern_returns_valid_pattern(): void
    {
        $this->assertSame('/^GET /i', Validation::ensureRegexPattern()('/^GET /i'));
    }

    public function test_ensure_regex_pattern_rejects_non_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be of type string');
        Validation::ensureRegexPattern()(1);
    }

    public function test_ensure_regex_pattern_rejects_invalid_pattern(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^must be a valid regex pattern: (?!preg_match\(\): )/');
        Validation::ensureRegexPattern()('/unclosed');
    }
}
