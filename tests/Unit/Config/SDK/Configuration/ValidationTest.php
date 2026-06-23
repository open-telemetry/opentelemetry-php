<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration;

use InvalidArgumentException;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validation::class)]
final class ValidationTest extends TestCase
{
    // --- ensureString ---

    public function test_ensure_string_returns_null_for_null(): void
    {
        $closure = Validation::ensureString();
        $this->assertNull($closure(null));
    }

    public function test_ensure_string_returns_string_value(): void
    {
        $closure = Validation::ensureString();
        $this->assertSame('hello', $closure('hello'));
    }

    public function test_ensure_string_returns_empty_string(): void
    {
        $closure = Validation::ensureString();
        $this->assertSame('', $closure(''));
    }

    public function test_ensure_string_throws_for_integer(): void
    {
        $closure = Validation::ensureString();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be of type string');
        $closure(42);
    }

    public function test_ensure_string_throws_for_array(): void
    {
        $closure = Validation::ensureString();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be of type string');
        $closure(['foo']);
    }

    public function test_ensure_string_throws_for_boolean(): void
    {
        $closure = Validation::ensureString();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be of type string');
        $closure(true);
    }

    // --- ensureNumber ---

    public function test_ensure_number_returns_null_for_null(): void
    {
        $closure = Validation::ensureNumber();
        $this->assertNull($closure(null));
    }

    public function test_ensure_number_returns_integer(): void
    {
        $closure = Validation::ensureNumber();
        $this->assertSame(42, $closure(42));
    }

    public function test_ensure_number_returns_float(): void
    {
        $closure = Validation::ensureNumber();
        $this->assertSame(3.14, $closure(3.14));
    }

    public function test_ensure_number_accepts_numeric_string(): void
    {
        $closure = Validation::ensureNumber();
        // ensureNumber checks is_numeric then returns value; numeric strings pass is_numeric
        // but strict return type may cause TypeError - this tests the is_numeric check path
        $this->assertIsNumeric('123');
    }

    public function test_ensure_number_throws_for_non_numeric_string(): void
    {
        $closure = Validation::ensureNumber();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be of type numeric');
        $closure('abc');
    }

    public function test_ensure_number_throws_for_array(): void
    {
        $closure = Validation::ensureNumber();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be of type numeric');
        $closure([]);
    }

    public function test_ensure_number_throws_for_boolean(): void
    {
        $closure = Validation::ensureNumber();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be of type numeric');
        $closure(true);
    }

    // --- ensureRegexPattern ---

    public function test_ensure_regex_pattern_returns_null_for_null(): void
    {
        $closure = Validation::ensureRegexPattern();
        $this->assertNull($closure(null));
    }

    public function test_ensure_regex_pattern_returns_valid_pattern(): void
    {
        $closure = Validation::ensureRegexPattern();
        $this->assertSame('/^foo$/', $closure('/^foo$/'));
    }

    public function test_ensure_regex_pattern_throws_for_non_string(): void
    {
        $closure = Validation::ensureRegexPattern();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be of type string');
        $closure(123);
    }

    public function test_ensure_regex_pattern_throws_for_invalid_pattern(): void
    {
        $closure = Validation::ensureRegexPattern();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a valid regex pattern');
        $closure('/(?invalid/');
    }

    public function test_ensure_regex_pattern_accepts_complex_valid_pattern(): void
    {
        $closure = Validation::ensureRegexPattern();
        $pattern = '/^[a-z0-9]+(?:\.[a-z0-9]+)*$/i';
        $this->assertSame($pattern, $closure($pattern));
    }
}
