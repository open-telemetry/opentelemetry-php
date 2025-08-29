<?php

declare(strict_types=1);
final 
namespace OpenTelemetry\Tests\Unit\Config\SDK\Parser;

use OpenTelemetry\Config\SDK\Parser\AttributesParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttributesParser::class)]
class AttributesParserTest extends TestCase
{
    public function test_parse_attributes_list(): void
    {
        $string = 'key1=value1,key2=value2';
        $expected = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $this->assertEquals($expected, AttributesParser::parseAttributesList($string));
    }

    public function test_apply_include_exclude(): void
    {
        $attributes = [
            'foo.key1' => 'value1',
            'foo.key2' => 'value2',
            'foo.key3' => 'value3',
            'bar.key1' => 'value4',
            'baz.key1' => 'value5',
            'baz.key2' => 'value6',
        ];
        $included = ['foo.*', 'ba?.*'];
        $excluded = ['*.key2'];

        $expected = [
            'foo.key1' => 'value1',
            'foo.key3' => 'value3',
            'bar.key1' => 'value4',
            'baz.key1' => 'value5',
        ];

        $this->assertEquals($expected, AttributesParser::applyIncludeExclude($attributes, $included, $excluded));
    }

    public function test_parse_attributes(): void
    {
        $input = [
            ['name' => 'service.name', 'value' => 'unknown_service'],
            ['name' => 'string_key', 'value' => 'value', 'type' => 'string'],
            ['name' => 'bool_key', 'value' => true, 'type' => 'bool'],
            ['name' => 'int_key', 'value' => 1, 'type' => 'int'],
            ['name' => 'double_key', 'value' => 1.1, 'type' => 'double'],
            ['name' => 'string_array_key', 'value' => ['value1', 'value2'], 'type' => 'string_array'],
            ['name' => 'bool_array_key', 'value' => [true, false], 'type' => 'bool_array'],
            ['name' => 'int_array_key', 'value' => [1, 2], 'type' => 'int_array'],
            ['name' => 'double_array_key', 'value' => [1.1, 2.2], 'type' => 'double_array'],
            ['name' => 'array_with_nulls', 'value' => [1, null, 2], 'type' => 'int_array'],
            ['name' => 'null', 'value' => null],
        ];
        $expected = [
            'service.name' => 'unknown_service',
            'string_key' => 'value',
            'bool_key' => true,
            'int_key' => 1,
            'double_key' => 1.1,
            'string_array_key' => ['value1', 'value2'],
            'bool_array_key' => [true, false],
            'int_array_key' => [1, 2],
            'double_array_key' => [1.1, 2.2],
            'array_with_nulls' => [1,2],
        ];

        /**
         * @psalm-suppress InvalidArgument
         * @phpstan-ignore-next-line
         */
        $this->assertEquals($expected, AttributesParser::parseAttributes($input));
    }

    public function test_empty_included(): void
    {
        $attributes = [
            'foo.key1' => 'value1',
        ];
        $included = [];
        $excluded = [];
        $expected = [];

        $this->assertEquals($expected, AttributesParser::applyIncludeExclude($attributes, $included, $excluded));
    }

    /**
     * "If omitted, all attributes are included"
     */
    public function test_null_included(): void
    {
        $attributes = [
            'foo.key1' => 'value1',
        ];
        $included = null;
        $excluded = [];
        $expected = [
            'foo.key1' => 'value1',
        ];

        $this->assertEquals($expected, AttributesParser::applyIncludeExclude($attributes, $included, $excluded));
    }
}
