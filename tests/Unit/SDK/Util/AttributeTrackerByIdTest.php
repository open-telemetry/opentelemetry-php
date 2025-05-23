<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Util;

use OpenTelemetry\SDK\Util\AttributeTrackerById;
use PHPUnit\Framework\TestCase;

class AttributeTrackerByIdTest extends TestCase
{
    private AttributeTrackerById $tracker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tracker = new AttributeTrackerById();
    }

    public function test_set(): void
    {
        $id = 'test_id_1';
        $attributes = ['key1' => 'value1', 'key2' => 123];
        $this->tracker->set($id, $attributes);
        $this->assertTrue($this->tracker->has($id));
        $this->assertEquals($attributes, $this->tracker->get($id));

        $newAttributes = ['key3' => true];
        $this->tracker->set($id, $newAttributes); // Overwrite
        $this->assertEquals($newAttributes, $this->tracker->get($id));
    }

    public function test_get(): void
    {
        $id = 'test_id_2';
        $this->assertEmpty($this->tracker->get($id)); // Should return empty array if not set

        $attributes = ['name' => 'John', 'age' => 30];
        $this->tracker->set($id, $attributes);
        $this->assertEquals($attributes, $this->tracker->get($id));
    }

    public function test_add(): void
    {
        $id = 'test_id_3';
        $initialAttributes = ['color' => 'red'];
        $this->tracker->add($id, $initialAttributes);
        $this->assertEquals($initialAttributes, $this->tracker->get($id));

        $additionalAttributes = ['size' => 'M', 'color' => 'blue']; // 'color' should be overwritten
        $result = $this->tracker->add($id, $additionalAttributes);
        $expected = ['color' => 'blue', 'size' => 'M']; // Note: order might vary but keys/values should match
        $this->assertEquals($expected, $this->tracker->get($id));
        $this->assertEquals($expected, $result); // 'add' method returns the merged array

        $id2 = 'test_id_4';
        $result2 = $this->tracker->add($id2, ['item' => 'book']);
        $this->assertEquals(['item' => 'book'], $this->tracker->get($id2));
        $this->assertEquals(['item' => 'book'], $result2);
    }

    public function test_reset(): void
    {
        $this->tracker->set('id1', ['a' => 1]);
        $this->tracker->set('id2', ['b' => 2]);
        $this->assertTrue($this->tracker->has('id1'));
        $this->assertTrue($this->tracker->has('id2'));

        $this->tracker->reset();
        $this->assertFalse($this->tracker->has('id1'));
        $this->assertFalse($this->tracker->has('id2'));
        $this->assertEmpty($this->tracker->get('id1'));
    }

    public function test_has(): void
    {
        $id = 'test_id_5';
        $this->assertFalse($this->tracker->has($id));

        $this->tracker->set($id, ['data' => 'some_data']);
        $this->assertTrue($this->tracker->has($id));

        $this->tracker->clear($id);
        $this->assertFalse($this->tracker->has($id));
    }

    public function test_append(): void
    {
        $id = 'test_id_6';
        $this->tracker->append($id, 'first_key', 'first_value');
        $this->assertEquals(['first_key' => 'first_value'], $this->tracker->get($id));

        $this->tracker->append($id, 'second_key', 456);
        $this->assertEquals(['first_key' => 'first_value', 'second_key' => 456], $this->tracker->get($id));

        $this->tracker->append($id, 'first_key', 'new_value'); // Overwrite
        $this->assertEquals(['first_key' => 'new_value', 'second_key' => 456], $this->tracker->get($id));

        // Test with a numeric key
        $this->tracker->append($id, 0, 'numeric_value');
        $this->assertEquals(['first_key' => 'new_value', 'second_key' => 456, 0 => 'numeric_value'], $this->tracker->get($id));
    }

    public function test_clear(): void
    {
        $id = 'test_id_7';
        $this->tracker->set($id, ['foo' => 'bar']);
        $this->assertTrue($this->tracker->has($id));
        $this->assertEquals(['foo' => 'bar'], $this->tracker->get($id));

        $this->tracker->clear($id);
        $this->assertFalse($this->tracker->has($id));
        $this->assertEmpty($this->tracker->get($id));

        // Clearing a non-existent ID should not cause an error
        $this->tracker->clear('non_existent_id');
        $this->assertFalse($this->tracker->has('non_existent_id'));
    }
}
