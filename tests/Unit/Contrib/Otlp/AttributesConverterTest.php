<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\AttributesConverter;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\Contrib\Otlp\AttributesConverter::class)]
class AttributesConverterTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('basicTypesProvider')]
    public function test_convert_basic_types($value, string $expected): void
    {
        $json = AttributesConverter::convertAnyValue($value)->serializeToJsonString();
        $this->assertJsonStringEqualsJsonString($json, $expected);
    }

    public static function basicTypesProvider(): array
    {
        return [
            'string' => ['foo', '{"stringValue":"foo"}'],
            'bool:true' => [true, '{"boolValue":true}'],
            'bool:false' => [false, '{"boolValue":false}'],
            'int:zero' => [0, '{"intValue":"0"}'],
            'int:positive' => [3, '{"intValue":"3"}'],
            'int:negative' => [-2, '{"intValue":"-2"}'],
            'float:zero' => [0.0, '{"doubleValue":0.0}'],
            'float:positive' => [3.14159, '{"doubleValue":3.14159}'],
            'float:negative' => [-2.7, '{"doubleValue":-2.7}'],
        ];
    }

    public function test_convert_bytes(): void
    {
        $anyValue = AttributesConverter::convertAnyValue("\xe2");
        $this->assertTrue($anyValue->hasBytesValue());
        $this->assertSame("\xe2", $anyValue->getBytesValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('arrayProvider')]
    public function test_is_simple_array(array $value, bool $expected): void
    {
        $this->assertSame($expected, AttributesConverter::isSimpleArray($value));
    }

    public static function arrayProvider(): array
    {
        return [
            'simple' => [[1,2,3,4], true],
            'empty' => [[], true],
            'kv' => [['key' => 'value'], false],
        ];
    }

    public function test_homogeneous_array_of_primitives(): void
    {
        $expected = '{"arrayValue":{"values":[{"intValue":"1"},{"intValue":"2"},{"intValue":"3"},{"intValue":"4"},{"intValue":"5"}]}}';
        $json = AttributesConverter::convertAnyValue([1,2,3,4,5])->serializeToJsonString();
        $this->assertJsonStringEqualsJsonString($json, $expected);
    }

    public function test_complex_array(): void
    {
        $expected = '{"kvlistValue":{"values":[{"key":"nested","value":{"arrayValue":{"values":[{"intValue":"123"},{"stringValue":"abc"},{"kvlistValue":{"values":[{"key":"sub","value":{"stringValue":"val"}}]}}]}}}]}}';
        $json = AttributesConverter::convertAnyValue(['nested' => [123, 'abc', ['sub'=>'val']]])->serializeToJsonString();
        $this->assertJsonStringEqualsJsonString($json, $expected);
    }

    public function test_kv_array_with_array_values(): void
    {
        $expected = '{"kvlistValue":{"values":[{"key":"nested","value":{"arrayValue":{"values":[{"intValue":"123"},{"intValue":"456"}]}}}]}}';
        $json = AttributesConverter::convertAnyValue(['nested' => [123, 456]])->serializeToJsonString();
        $this->assertJsonStringEqualsJsonString($json, $expected);
    }
}
