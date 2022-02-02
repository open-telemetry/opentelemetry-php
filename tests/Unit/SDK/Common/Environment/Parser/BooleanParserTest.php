<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Environment\Parser;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Environment\Parser\BooleanParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Environment\Parser\BooleanParser
 */
class BooleanParserTest extends TestCase
{
    private const TRUTHY_VALUES = [
        'bool uppercase' => ['TRUE'],
        'bool lowercase' => ['true'],
        'state uppercase' => ['ON'],
        'state lowercase' => ['on'],
        'int' => ['1'],
    ];

    private const FALSY_VALUES = [
        'bool uppercase' => ['FALSE'],
        'bool lowercase' => ['false'],
        'state uppercase' => ['OFF'],
        'state lowercase' => ['off'],
        'int' => ['0'],
    ];

    private const NON_BOOLEAN_VALUES = [
        'string' => ['Foo'],
        'int' => ['42'],
        'float' => ['0.5'],
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
     * @dataProvider nonBooleanValueProvider
     */
    public function test_non_boolean_values_throw_exception(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        BooleanParser::parse($value);
    }

    public function truthyValueProvider(): array
    {
        return self::TRUTHY_VALUES;
    }

    public function falsyValueProvider(): array
    {
        return self::FALSY_VALUES;
    }

    public function nonBooleanValueProvider(): array
    {
        return self::NON_BOOLEAN_VALUES;
    }
}
