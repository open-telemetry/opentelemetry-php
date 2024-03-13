<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\Parser;

use InvalidArgumentException;
use OpenTelemetry\Config\Parser\BooleanParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Config\Parser\BooleanParser
 */
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

    /**
     * @dataProvider truthyValueProvider
     */
    public function test_truthy_values_return_true(string $value): void
    {
        $this->assertTrue(
            BooleanParser::parse($value)
        );
    }

    /**
     * @dataProvider falsyValueProvider
     */
    public function test_falsy_values_return_false(string $value): void
    {
        $this->assertFalse(
            BooleanParser::parse($value)
        );
    }

    /**
     * @dataProvider disallowedBooleanProvider
     */
    public function test_disallowed_boolean_type_values_throw_exception(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        BooleanParser::parse($value);
    }

    public static function disallowedBooleanProvider(): array
    {
        return self::DISALLOWED_BOOLEAN_VALUES;
    }

    /**
     * @dataProvider nonBooleanValueProvider
     */
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

    /**
     * @dataProvider boolProvider
     */
    public function test_boolean(bool $bool): void
    {
        $this->assertSame($bool, BooleanParser::parse($bool));
    }

    public static function boolProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
