<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\SDK\Attributes;
use PHPUnit\Framework\TestCase;

class AttributesTest extends TestCase
{
    /** @test Test numeric attribute key is not cast to integer value */
    public function test_numeric_attribute_name(): void
    {
        $attributes = Attributes::create((fn () => yield '1' => '2')());
        $this->assertCount(1, $attributes);
        foreach ($attributes as $key => $value) {
            $this->assertTrue(is_string($key));
            $this->assertTrue(is_string($value));
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

        $attributes = Attributes::factory(6, 16)
            ->builder([
                'bool' => $boolValue,
                'int' => $intValue,
                'float' => $floatValue,
                'short_string' => $shortStringValue,
                'long_string' => $longStringValue,
                'array' => [
                    $shortStringValue,
                    $longStringValue,
                ],
                'ignored_key' => 'ignored_value',
            ])
            ->build();

        $this->assertEquals($boolValue, $attributes->get('bool'));
        $this->assertEquals($intValue, $attributes->get('int'));
        $this->assertEquals($floatValue, $attributes->get('float'));
        $this->assertEquals($shortStringValue, $attributes->get('short_string'));
        $this->assertEquals($longStringTrimmed, $attributes->get('long_string'));
        $this->assertEquals([$shortStringValue, $longStringTrimmed], $attributes->get('array'));

        $this->assertEquals(6, $attributes->count());
        $this->assertNull($attributes->get('ignored_key'));
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function test_apply_limits(): void
    {
        $limitedAttributes = Attributes::factory(2, 5)
            ->builder([
                'short' => '123',
                'long' => '1234567890',
                'dropped' => true,
            ])
            ->build();
        $this->assertCount(2, $limitedAttributes);
        $this->assertEquals('123', $limitedAttributes->get('short'));
        $this->assertEquals('12345', $limitedAttributes->get('long'));
        $this->assertNull($limitedAttributes->get('dropped'));
    }

    public function test_builder_from_iterable(): void
    {
        $attributes = Attributes::factory()->builder((fn () => yield 'key' => 'value')());

        $this->assertTrue(isset($attributes['key']));
        $this->assertSame('value', $attributes['key']);

        $attributes = $attributes->build();
        $this->assertSame('value', $attributes->get('key'));
        $this->assertCount(1, $attributes);
        $this->assertSame(0, $attributes->getDroppedAttributesCount());
    }

    public function test_builder_ignores_null_values(): void
    {
        $attributes = Attributes::factory(1)->builder([
            'key' => null,
            'foo' => 'bar',
        ]);

        $this->assertFalse(isset($attributes['key']));
        $this->assertSame('bar', $attributes['foo']);

        $attributes = $attributes->build();
        $this->assertCount(1, $attributes);
        $this->assertSame(0, $attributes->getDroppedAttributesCount());
    }

    public function test_unset_after_count_limit_enables_set(): void
    {
        $attributes = Attributes::factory(2)->builder([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
        ]);

        unset($attributes['a']);
        $attributes['d'] = 'd';
        $attributes['e'] = 'e';

        $attributes = $attributes->build();
        $this->assertCount(2, $attributes);
        $this->assertSame('b', $attributes->get('b'));
        $this->assertSame('d', $attributes->get('d'));
        $this->assertSame(2, $attributes->getDroppedAttributesCount());
    }
}
