<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use ArrayObject;
use OpenTelemetry\Sdk\Trace\PropagationMap;
use PHPUnit\Framework\TestCase;
use stdClass;

class PropagationMapTest extends TestCase
{
    /**
     * @test
     */
    public function testGetFromMapArray()
    {
        $carrier = ['a' => 'alpha'];
        $map = new PropagationMap();

        $value = $map->get($carrier, 'a');
        $this->assertSame('alpha', $value);
    }

    /**
     * @test
     */
    public function testGetFromMapArrayAccess()
    {
        $carrier = new ArrayObject(['a' => 'alpha']);
        $map = new PropagationMap();

        $value = $map->get($carrier, 'a');
        $this->assertSame('alpha', $value);
    }

    /**
     * @test
     */
    public function testGetFromUnsupportedCarrier()
    {
        $carrier = new stdClass();
        $map = new PropagationMap();
        $this->expectException(\InvalidArgumentException::class);
        $map->get($carrier, 'a');
    }

    /**
     * @test
     */
    public function testInvalidGetFromMap()
    {
        $carrier = ['a' => 'alpha'];
        $map = new PropagationMap();

        $this->assertNull($map->get([], 'a'));
        $this->assertNull($map->get($carrier, 'b'));
         
        $this->expectException(\InvalidArgumentException::class);
        $value = $map->get('invalid carrier', 'a');
    }

    /**
     * @test
     */
    public function testSetMapArray()
    {
        $carrier = ['a' => 'alpha'];
        $map = new PropagationMap();

        $map->set($carrier, 'b', 'bravo');
        $value = $map->get($carrier, 'b');
        $this->assertSame('bravo', $value);
    }

    /**
     * @test
     */
    public function testSetMapArrayAccess()
    {
        $carrier = new ArrayObject(['a' => 'alpha']);
        $map = new PropagationMap();

        $map->set($carrier, 'b', 'bravo');
        $value = $map->get($carrier, 'b');
        $this->assertSame('bravo', $value);
    }

    /**
     * @test
     */
    public function testSetUnsupportedCarrier()
    {
        $carrier = new stdClass();
        $map = new PropagationMap();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid carrier of type ' . \get_class($carrier) . '. Unable to set value associated with key:a');
        $map->set($carrier, 'a', 'alpha');
    }

    /**
     * @test
     */
    public function testSetEmptyKey()
    {
        $carrier = ['a' => 'alpha'];
        $map = new PropagationMap();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to set value with an empty key');
        $map->set($carrier, '', 'alpha');
    }
}
