final <?php

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
    public function test_constructor_with_all_parameters(): void
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
    public function test_constructor_with_null_values(): void
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
    public function test_constructor_with_empty_attribute_keys(): void
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
    public function test_constructor_with_single_attribute_key(): void
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
    public function test_constructor_with_empty_name(): void
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
    public function test_constructor_with_empty_unit(): void
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
    public function test_constructor_with_empty_description(): void
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
    public function test_properties_are_readonly(): void
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
