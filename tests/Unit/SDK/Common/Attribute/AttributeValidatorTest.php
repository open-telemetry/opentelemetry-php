<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Attribute;

use OpenTelemetry\SDK\Common\Attribute\AttributeValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Attribute\AttributeValidator
 */
class AttributeValidatorTest extends TestCase
{
    private AttributeValidator $validator;

    public function setUp(): void
    {
        $this->validator = new AttributeValidator();
    }

    /**
     * @dataProvider primitiveProvider
     */
    public function test_validate_primitives($value): void
    {
        $this->assertTrue($this->validator->validate($value));
    }

    public static function primitiveProvider(): array
    {
        return [
            'bool true' => [true],
            'bool false' => [false],
            'string' => ['hello otel'],
            'int' => [4],
            'double' => [3.14159],
        ];
    }

    /**
     * @dataProvider nonPrimitiveProvider
     */
    public function test_validate_non_primitives($value): void
    {
        $this->assertFalse($this->validator->validate($value));
    }

    public static function nonPrimitiveProvider(): array
    {
        return [
            'object' => [new \stdClass()],
            'null' => [null],
            'resource' => [tmpfile()],
        ];
    }

    /**
     * @dataProvider arrayProvider
     */
    public function test_validate_array($value, bool $expected): void
    {
        $this->assertSame($expected, $this->validator->validate($value));
    }

    public static function arrayProvider(): array
    {
        return [
            'empty array' => [[], true],
            'array of strings' => [['one', 'two'], true],
            'array of ints' => [[1, 2], true],
            'array of double' => [[1.1, 1.2], true],
            'mixed numerics' => [[2, 3.5, PHP_INT_MAX], true],
            'array of bool' => [[true, false], true],
            'mixed array' => [[true, 'one', 2], false],
            'complex array' => [['one' => ['one']], false],
        ];
    }
}
