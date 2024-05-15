<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Attribute;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\FilteredAttributesBuilder;
use OpenTelemetry\SDK\Common\Attribute\FilteredAttributesFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilteredAttributesBuilder::class)]
#[CoversClass(FilteredAttributesFactory::class)]
class FilteredAttributesTest extends TestCase
{
    public function test_rejected_keys_are_dropped(): void
    {
        $attributesFactory = new FilteredAttributesFactory(Attributes::factory(), ['bar']);
        $attributesBuilder = $attributesFactory->builder([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
        ]);

        $attributes = $attributesBuilder->build();
        $this->assertCount(2, $attributes);
        $this->assertSame(1, $attributes->getDroppedAttributesCount());
        $this->assertTrue($attributes->has('foo'));
        $this->assertFalse($attributes->has('bar'));
        $this->assertTrue($attributes->has('baz'));
    }

    public function test_rejected_key_null_value_does_not_increase_dropped_count(): void
    {
        $attributesFactory = new FilteredAttributesFactory(Attributes::factory(), ['bar']);
        $attributesBuilder = $attributesFactory->builder([
            'bar' => null,
        ]);

        $attributes = $attributesBuilder->build();
        $this->assertCount(0, $attributes);
        $this->assertSame(0, $attributes->getDroppedAttributesCount());
    }
}
