<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Attribute;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Attribute\Attributes
 * @covers \OpenTelemetry\SDK\Common\Attribute\AttributesBuilder
 * @covers \OpenTelemetry\SDK\Common\Attribute\AttributesFactory
 */
class AttributesTest extends TestCase
{
    public function test_has_attribute(): void
    {
        $attributes = Attributes::create([
            'foo' => 'foo',
        ]);

        $this->assertFalse($attributes->has('bar'));

        $attributes = Attributes::create([
            'foo' => 'foo',
            'bar' => 'bar',
        ]);

        $this->assertTrue($attributes->has('bar'));
    }

    public function test_integer_attribute_key_is_cast_to_string(): void
    {
        $attributes = Attributes::create([1 => 2]);
        $this->assertCount(1, $attributes);
        foreach ($attributes as $key => $value) {
            $this->assertIsString($key);
            $this->assertSame('1', $key);
        }
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function test_attribute_limits(): void
    {
        $intValue = 42;
        $floatValue = 3.14;
        $shortStringValue = '0123';
        $longStringValue = '0123456789abcdefghijklmnopqrstuvwxyz';
        $longStringTrimmed = '0123456789abcdef';

        $attributes = Attributes::factory(6, 16)->builder([
            'bool' => true,
            'int' => $intValue,
            'float' => $floatValue,
            'short_string' => $shortStringValue,
            'long_string' => $longStringValue,
            'array' => [
                $shortStringValue,
                $longStringValue,
                true,
            ],
            'ignored_key' => 'ignored_value',
        ])->build();

        $this->assertTrue($attributes->get('bool'));
        $this->assertEquals($intValue, $attributes->get('int'));
        $this->assertEquals($floatValue, $attributes->get('float'));
        $this->assertEquals($shortStringValue, $attributes->get('short_string'));
        $this->assertEquals($longStringTrimmed, $attributes->get('long_string'));
        $this->assertEquals([$shortStringValue, $longStringTrimmed, true], $attributes->get('array'));
        $this->assertEquals(6, $attributes->count());
        $this->assertNull($attributes->get('ignored_key'));
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function test_apply_limits(): void
    {
        $attributes = Attributes::create([
            'short' => '123',
            'long' => '1234567890',
            'dropped' => true,
        ]);
        $limitedAttributes = Attributes::factory(2, 5)->builder($attributes)->build();
        $this->assertCount(2, $limitedAttributes);
        $this->assertEquals('123', $limitedAttributes->get('short'));
        $this->assertEquals('12345', $limitedAttributes->get('long'));
        $this->assertNull($limitedAttributes->get('dropped'));
        $this->assertGreaterThan(0, $limitedAttributes->getDroppedAttributesCount());
    }

    public function test_null_attribute_removes_existing(): void
    {
        $attributesBuilder = Attributes::factory()->builder([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
        ]);
        $this->assertCount(3, $attributesBuilder->build());
        $attributesBuilder['foo'] = null;
        $this->assertCount(2, $attributesBuilder->build());
    }

    public function test_null_missing_attribute_does_nothing(): void
    {
        $attributesBuilder = Attributes::factory()->builder([
            'foo' => 'foo',
        ]);
        $this->assertCount(1, $attributesBuilder->build());
        $attributesBuilder['bar'] = null;
        $this->assertCount(1, $attributesBuilder->build());
    }

    public function test_to_array(): void
    {
        $values = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];
        $attributes = Attributes::create($values);
        $this->assertSame($values, $attributes->toArray());
    }

    public function test_get_dropped_attributes_count(): void
    {
        $attributesBuilder = Attributes::factory()->builder([
            'foo' => 'foo',
            'bar' => 'bar',
        ]);
        $this->assertEquals(0, $attributesBuilder->build()->getDroppedAttributesCount());

        $attributesBuilder['baz'] = 'baz';
        $this->assertEquals(0, $attributesBuilder->build()->getDroppedAttributesCount());
    }

    public function test_unset_get_dropped_attributes_count(): void
    {
        $attributesBuilder = Attributes::factory()->builder([
            'foo' => 'foo',
            'bar' => 'bar',
        ]);
        $this->assertEquals(0, $attributesBuilder->build()->getDroppedAttributesCount());

        $attributesBuilder->offsetUnset('foo');
        $this->assertEquals(0, $attributesBuilder->build()->getDroppedAttributesCount());
    }

    public function test_limit_get_dropped_attributes_count(): void
    {
        $attributesBuilder = Attributes::factory(1)->builder([
            'foo' => 'foo',
            'bar' => 'bar',
        ]);
        $this->assertEquals(1, $attributesBuilder->build()->getDroppedAttributesCount());

        $attributesBuilder['baz'] = 'baz';
        $this->assertEquals(2, $attributesBuilder->build()->getDroppedAttributesCount());
    }

    public function test_replace_attribute_does_not_increase_dropped_attributes_count(): void
    {
        $attributesBuilder = Attributes::factory(2)->builder([
            'foo' => 'foo',
        ]);

        $attributesBuilder['foo'] = 'bar';
        $this->assertEquals(0, $attributesBuilder->build()->getDroppedAttributesCount());
    }
}
