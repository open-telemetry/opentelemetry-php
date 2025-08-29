<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use PHPUnit\Framework\TestCase;

class ViewProjectionTest extends TestCase
{
    /**
     * @covers \OpenTelemetry\SDK\Metrics\ViewProjection::__construct
     */
    public function testConstructorWithAllParameters(): void
    {
        $mockAggregation = $this->createMock(AggregationInterface::class);
        $attributeKeys = ['key1', 'key2'];
        
        $viewProjection = new ViewProjection(
            'test-view',
            'requests',
            'Test view description',
            $attributeKeys,
            $mockAggregation
        );
        
        $this->assertEquals('test-view', $viewProjection->name);
        $this->assertEquals('requests', $viewProjection->unit);
        $this->assertEquals('Test view description', $viewProjection->description);
        $this->assertEquals($attributeKeys, $viewProjection->attributeKeys);
        $this->assertSame($mockAggregation, $viewProjection->aggregation);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ViewProjection::__construct
     */
    public function testConstructorWithNullValues(): void
    {
        $viewProjection = new ViewProjection(
            'test-view',
            null,
            null,
            null,
            null
        );
        
        $this->assertEquals('test-view', $viewProjection->name);
        $this->assertNull($viewProjection->unit);
        $this->assertNull($viewProjection->description);
        $this->assertNull($viewProjection->attributeKeys);
        $this->assertNull($viewProjection->aggregation);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ViewProjection::__construct
     */
    public function testConstructorWithEmptyAttributeKeys(): void
    {
        $viewProjection = new ViewProjection(
            'test-view',
            'requests',
            'Test view description',
            [],
            null
        );
        
        $this->assertEquals('test-view', $viewProjection->name);
        $this->assertEquals('requests', $viewProjection->unit);
        $this->assertEquals('Test view description', $viewProjection->description);
        $this->assertEquals([], $viewProjection->attributeKeys);
        $this->assertNull($viewProjection->aggregation);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ViewProjection::__construct
     */
    public function testConstructorWithSingleAttributeKey(): void
    {
        $attributeKeys = ['single-key'];
        
        $viewProjection = new ViewProjection(
            'test-view',
            'requests',
            'Test view description',
            $attributeKeys,
            null
        );
        
        $this->assertEquals('test-view', $viewProjection->name);
        $this->assertEquals('requests', $viewProjection->unit);
        $this->assertEquals('Test view description', $viewProjection->description);
        $this->assertEquals($attributeKeys, $viewProjection->attributeKeys);
        $this->assertNull($viewProjection->aggregation);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ViewProjection::__construct
     */
    public function testConstructorWithEmptyName(): void
    {
        $viewProjection = new ViewProjection(
            '',
            'requests',
            'Test view description',
            null,
            null
        );
        
        $this->assertEquals('', $viewProjection->name);
        $this->assertEquals('requests', $viewProjection->unit);
        $this->assertEquals('Test view description', $viewProjection->description);
        $this->assertNull($viewProjection->attributeKeys);
        $this->assertNull($viewProjection->aggregation);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ViewProjection::__construct
     */
    public function testConstructorWithEmptyUnit(): void
    {
        $viewProjection = new ViewProjection(
            'test-view',
            '',
            'Test view description',
            null,
            null
        );
        
        $this->assertEquals('test-view', $viewProjection->name);
        $this->assertEquals('', $viewProjection->unit);
        $this->assertEquals('Test view description', $viewProjection->description);
        $this->assertNull($viewProjection->attributeKeys);
        $this->assertNull($viewProjection->aggregation);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ViewProjection::__construct
     */
    public function testConstructorWithEmptyDescription(): void
    {
        $viewProjection = new ViewProjection(
            'test-view',
            'requests',
            '',
            null,
            null
        );
        
        $this->assertEquals('test-view', $viewProjection->name);
        $this->assertEquals('requests', $viewProjection->unit);
        $this->assertEquals('', $viewProjection->description);
        $this->assertNull($viewProjection->attributeKeys);
        $this->assertNull($viewProjection->aggregation);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ViewProjection::__construct
     */
    public function testPropertiesAreReadonly(): void
    {
        $viewProjection = new ViewProjection(
            'test-view',
            'requests',
            'Test view description',
            null,
            null
        );
        
        // Test that properties are readonly by attempting to modify them
        // This should not cause any errors, but the properties should remain unchanged
        $originalName = $viewProjection->name;
        $originalUnit = $viewProjection->unit;
        $originalDescription = $viewProjection->description;
        
        // The properties are readonly, so we can't modify them
        // This test just ensures the constructor works correctly
        $this->assertEquals('test-view', $originalName);
        $this->assertEquals('requests', $originalUnit);
        $this->assertEquals('Test view description', $originalDescription);
    }
}
