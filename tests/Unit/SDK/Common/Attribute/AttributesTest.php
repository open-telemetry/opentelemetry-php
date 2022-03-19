<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Attribute;

use OpenTelemetry\SDK\Common\Attribute\AttributeLimits;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Attribute\Attributes
 */
class AttributesTest extends TestCase
{
    public function test_attribute_limits_compare(): void
    {
        $attrLimits1 = new AttributeLimits(10, 20);
        $attrLimits2 = new AttributeLimits(10, 20);
        $attrLimits3 = new AttributeLimits(20, 30);

        $this->assertEquals($attrLimits1, $attrLimits2);
        $this->assertNotEquals($attrLimits1, $attrLimits3);
    }

    /** Test numeric attribute key is not cast to integer value */
    public function test_numeric_attribute_name(): void
    {
        $attributes = new Attributes(['1' => '2']);
        $this->assertCount(1, $attributes);
        foreach ($attributes as $key => $value) {
            $this->assertIsString($key);
            $this->assertIsString($value);
        }
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function test_attribute_limits(): void
    {
        $boolValue = true;
        $intValue = 42;
        $floatValue = 3.14;
        $shortStringValue = '0123';
        $longStringValue = '0123456789abcdefghijklmnopqrstuvwxyz';
        $longStringTrimmed = '0123456789abcdef';

        $attributeLimits = new AttributeLimits(6, 16);
        $attributes = new Attributes([
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
        ], $attributeLimits);

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
        $attributes = new Attributes([
            'short' => '123',
            'long' => '1234567890',
            'dropped' => true,
        ]);
        $limitedAttributes = Attributes::withLimits($attributes, new AttributeLimits(2, 5));
        $this->assertCount(2, $limitedAttributes);
        $this->assertEquals('123', $limitedAttributes->get('short'));
        $this->assertEquals('12345', $limitedAttributes->get('long'));
        $this->assertNull($limitedAttributes->get('dropped'));
        $this->assertGreaterThan(0, $limitedAttributes->getDroppedAttributesCount());
    }

    public function test_null_attribute_removes_existing(): void
    {
        $attributes = new Attributes([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
        ]);
        $this->assertCount(3, $attributes);
        $attributes->setAttribute('foo', null);
        $this->assertCount(2, $attributes);
    }

    public function test_null_missing_attribute_does_nothing(): void
    {
        $attributes = new Attributes([
            'foo' => 'foo',
        ]);
        $this->assertCount(1, $attributes);
        $attributes->setAttribute('bar', null);
        $this->assertCount(1, $attributes);
    }
    public function test_to_array(): void
    {
        $values = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];
        $attributes = new Attributes($values);
        $this->assertSame($values, $attributes->toArray());
        $this->assertEquals(2, $attributes->getTotalAddedValues());
    }
}
