<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\SDK\Trace\AttributeLimits;
use OpenTelemetry\SDK\Trace\Attributes;
use PHPUnit\Framework\TestCase;

class AttributesTest extends TestCase
{
    public function testAttributeLimitsCompare()
    {
        $attrLimits1 = new AttributeLimits(10, 20);
        $attrLimits2 = new AttributeLimits(10, 20);
        $attrLimits3 = new AttributeLimits(20, 30);

        $this->assertTrue($attrLimits1 == $attrLimits2);
        $this->assertTrue($attrLimits1 != $attrLimits3);
    }

    /** @test Test numeric attribute key is not cast to integer value */
    public function testNumericAttributeName()
    {
        $attributes = new Attributes(['1' => '2']);
        $this->assertCount(1, $attributes);
        foreach ($attributes as $attribute) {
            $this->assertTrue(is_string($attribute->getKey()));
            $this->assertTrue(is_string($attribute->getValue()));
        }
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function testAttributeLimits()
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

        $this->assertEquals($boolValue, $attributes->getAttribute('bool')->getValue());
        $this->assertEquals($intValue, $attributes->getAttribute('int')->getValue());
        $this->assertEquals($floatValue, $attributes->getAttribute('float')->getValue());
        $this->assertEquals($shortStringValue, $attributes->getAttribute('short_string')->getValue());
        $this->assertEquals($longStringTrimmed, $attributes->getAttribute('long_string')->getValue());
        $this->assertEquals([$shortStringValue, $longStringTrimmed], $attributes->getAttribute('array')->getValue());

        $this->assertEquals(6, $attributes->count());
        $this->assertNull($attributes->getAttribute('ignored_key'));
    }

    /**
     * @psalm-suppress PossiblyNullReference
     */
    public function testApplyLimits()
    {
        $attributes = new Attributes([
            'short' => '123',
            'long' => '1234567890',
            'dropped' => true,
        ]);
        $limitedAttributes = Attributes::withLimits($attributes, new AttributeLimits(2, 5));
        $this->assertCount(2, $limitedAttributes);
        $this->assertEquals('123', $limitedAttributes->getAttribute('short')->getValue());
        $this->assertEquals('12345', $limitedAttributes->getAttribute('long')->getValue());
        $this->assertNull($limitedAttributes->getAttribute('dropped'));
    }
}
