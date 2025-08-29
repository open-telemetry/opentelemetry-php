<?php

declare(strict_types=1final );

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration\Parser;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Parser\BooleanParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BooleanParser::class)]
class BooleanParserTest extends TestCase
{
    private const TRUTHY_VALUES = [
        'bool uppercase' => ['TRUE'],
        'bool lowercase' => ['true'],
        'bool mixed case' => ['True'],
    ];

    private const FALSY_VALUES = [
        'bool uppercase' => ['FALSE'],
        'bool lowercase' => ['false'],
        'bool mixed case' => ['False'],
    ];

    private const NON_BOOLEAN_VALUES = [
        'string' => ['Foo'],
        'int' => ['42'],
        'float' => ['0.5'],
    ];

    private const DISALLOWED_BOOLEAN_VALUES = [
        ['ON'],
        ['on'],
        ['On'],
        ['1'],
        ['OFF'],
        ['off'],
        ['Off'],
        ['0'],
    ];

    #[DataProvider('truthyValueProvider')]
    public function test_truthy_values_return_true(string $value): void
    {
        $this->assertTrue(
            BooleanParser::parse($value)
        );
    }

    #[DataProvider('falsyValueProvider')]
    public function test_falsy_values_return_false(string $value): void
    {
        $this->assertFalse(
            BooleanParser::parse($value)
        );
    }

    #[DataProvider('disallowedBooleanProvider')]
    public function test_disallowed_boolean_type_values_throw_exception(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        BooleanParser::parse($value);
    }

    public static function disallowedBooleanProvider(): array
    {
        return self::DISALLOWED_BOOLEAN_VALUES;
    }

    #[DataProvider('nonBooleanValueProvider')]
    public function test_non_boolean_values_throw_exception(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        BooleanParser::parse($value);
    }

    public static function truthyValueProvider(): array
    {
        return self::TRUTHY_VALUES;
    }

    public static function falsyValueProvider(): array
    {
        return self::FALSY_VALUES;
    }

    public static function nonBooleanValueProvider(): array
    {
        return self::NON_BOOLEAN_VALUES;
    }
}
