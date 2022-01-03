<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit;

use OpenTelemetry\SDK\AttributeLimits;
use OpenTelemetry\SDK\Attributes;
use PHPUnit\Framework\TestCase;

class AttributesTest extends TestCase
{
    public function test_attribute_limits_compare()
    {
        $attrLimits1 = new AttributeLimits(10, 20);
        $attrLimits2 = new AttributeLimits(10, 20);
        $attrLimits3 = new AttributeLimits(20, 30);

        $this->assertTrue($attrLimits1 == $attrLimits2);
        $this->assertTrue($attrLimits1 != $attrLimits3);
    }

    /** @test Test numeric attribute key is not cast to integer value */
    public function test_numeric_attribute_name()
    {
        $attributes = new Attributes(['1' => '2']);
        $this->assertCount(1, $attributes);
        foreach ($attributes as $key => $value) {
            $this->assertTrue(is_string($key));
            $this->assertTrue(is_string($value));
        }
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function test_attribute_limits()
    {
        $boolValue = true;
        $intValue = 42;
        $floatValue = 3.14;
        $shortStringValue = '0123';
        $longStringValue = '0123456789abcdefghijklmnopqrstuvwxyz';
        $longStringTrimmed = '0123456789abcdef';

        $attributeLimits = new AttributeLimits(6, 16);
        $attributes = new Attributes([
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
        ], $attributeLimits);

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
    public function test_apply_limits()
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
    }
}
