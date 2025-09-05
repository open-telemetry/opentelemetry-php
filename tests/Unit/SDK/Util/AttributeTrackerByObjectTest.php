<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Util;

use OpenTelemetry\SDK\Util\AttributeTrackerByObject;
use PHPUnit\Framework\TestCase;

class AttributeTrackerByObjectTest extends TestCase
{
    private AttributeTrackerByObject $tracker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tracker = new AttributeTrackerByObject();
    }

    public function test_set(): void
    {
        $id = new \stdClass();
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
        $id = new \stdClass();
        $this->assertEmpty($this->tracker->get($id)); // Should return empty array if not set

        $attributes = ['name' => 'Jane', 'age' => 25];
        $this->tracker->set($id, $attributes);
        $this->assertEquals($attributes, $this->tracker->get($id));
    }

    public function test_add(): void
    {
        $id = new \stdClass();
        $initialAttributes = ['color' => 'green'];
        $this->tracker->add($id, $initialAttributes);
        $this->assertEquals($initialAttributes, $this->tracker->get($id));

        $additionalAttributes = ['size' => 'L', 'color' => 'yellow']; // 'color' should be overwritten
        $result = $this->tracker->add($id, $additionalAttributes);
        $expected = ['color' => 'yellow', 'size' => 'L'];
        $this->assertEquals($expected, $this->tracker->get($id));
        $this->assertEquals($expected, $result); // 'add' method returns the merged array

        $id2 = new \stdClass();
        $result2 = $this->tracker->add($id2, ['item' => 'pen']);
        $this->assertEquals(['item' => 'pen'], $this->tracker->get($id2));
        $this->assertEquals(['item' => 'pen'], $result2);
    }

    public function test_reset(): void
    {
        $id1 = new \stdClass();
        $id2 = new \stdClass();
        $this->tracker->set($id1, ['a' => 1]);
        $this->tracker->set($id2, ['b' => 2]);
        $this->assertTrue($this->tracker->has($id1));
        $this->assertTrue($this->tracker->has($id2));

        $this->tracker->reset();
        $this->assertFalse($this->tracker->has($id1)); // WeakMap might still hold references for a short while
        $this->assertFalse($this->tracker->has($id2)); // but functionally, it's reset
        $this->assertEmpty($this->tracker->get($id1));
    }

    public function test_has(): void
    {
        $id = new \stdClass();
        $this->assertFalse($this->tracker->has($id));

        $this->tracker->set($id, ['data' => 'some_object_data']);
        $this->assertTrue($this->tracker->has($id));

        $this->tracker->clear($id);
        $this->assertFalse($this->tracker->has($id));
    }

    public function test_append(): void
    {
        $id = new \stdClass();
        $this->tracker->append($id, 'first_key', 'first_value');
        $this->assertEquals(['first_key' => 'first_value'], $this->tracker->get($id));

        $this->tracker->append($id, 'second_key', 789);
        $this->assertEquals(['first_key' => 'first_value', 'second_key' => 789], $this->tracker->get($id));

        $this->tracker->append($id, 'first_key', 'another_value'); // Overwrite
        $this->assertEquals(['first_key' => 'another_value', 'second_key' => 789], $this->tracker->get($id));

        // Test with a numeric key
        $this->tracker->append($id, 0, 'numeric_object_value');
        $this->assertEquals(['first_key' => 'another_value', 'second_key' => 789, 0 => 'numeric_object_value'], $this->tracker->get($id));
    }

    public function test_clear(): void
    {
        $id = new \stdClass();
        $this->tracker->set($id, ['bar' => 'baz']);
        $this->assertTrue($this->tracker->has($id));
        $this->assertEquals(['bar' => 'baz'], $this->tracker->get($id));

        $this->tracker->clear($id);
        $this->assertFalse($this->tracker->has($id));
        $this->assertEmpty($this->tracker->get($id));

        // Clearing a non-existent ID should not cause an error
        $nonExistentId = new \stdClass();
        $this->tracker->clear($nonExistentId);
        $this->assertFalse($this->tracker->has($nonExistentId));
    }
}
