<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\Propagation;

use ArrayObject;
use InvalidArgumentException;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter
 */
class ArrayAccessGetterSetterTest extends TestCase
{
    public function test_get_from_map_array(): void
    {
        $carrier = ['a' => 'alpha'];
        $map = new ArrayAccessGetterSetter();

        $this->assertSame('alpha', $map->get($carrier, 'a'));
        $this->assertSame(['a'], $map->keys($carrier));
    }

    public function test_get_array_values_from_carrier(): void
    {
        // Carrier contains an array as one of the values
        $carrier = [
            'a' => 'alpha',
            'b' => ['bravo'],
        ];
        $map = new ArrayAccessGetterSetter();
        $this->assertSame('alpha', $map->get($carrier, 'a'));
        $this->assertSame('bravo', $map->get($carrier, 'b'));
        $this->assertSame(['a', 'b'], $map->keys($carrier));
    }

    public function test_get_numerical_key_from_carrier(): void
    {
        // Carrier contains an array as one of the values
        $carrier = [
            1 => ['alpha'],
            'b' => 'bravo',
        ];
        $map = new ArrayAccessGetterSetter();
        $this->assertSame('alpha', $map->get($carrier, '1'));
        $this->assertSame('bravo', $map->get($carrier, 'b'));
        $this->assertSame(['1', 'b'], $map->keys($carrier));
    }

    public function test_get_from_unsupported_carrier(): void
    {
        $carrier = new stdClass();
        $map = new ArrayAccessGetterSetter();
        $this->expectException(InvalidArgumentException::class);
        $map->get($carrier, 'a');
    }

    public function test_keys_from_map_array_access(): void
    {
        $carrier = new ArrayObject(['a' => 'alpha', 'b' => 'bravo']);

        $map = new ArrayAccessGetterSetter();
        $this->assertSame(['a', 'b'], $map->keys($carrier));
    }

    public function test_keys_from_unsupported_carrier(): void
    {
        $carrier = new stdClass();
        $map = new ArrayAccessGetterSetter();
        $this->expectException(InvalidArgumentException::class);
        $map->keys($carrier);
    }

    public function test_set_map_array(): void
    {
        $carrier = ['a' => 'alpha'];
        $map = new ArrayAccessGetterSetter();

        $map->set($carrier, 'b', 'bravo');
        $value = $map->get($carrier, 'b');
        $this->assertSame('bravo', $value);
    }

    public function test_set_map_array_access(): void
    {
        $carrier = new ArrayObject(['a' => 'alpha']);
        $map = new ArrayAccessGetterSetter();

        $map->set($carrier, 'b', 'bravo');
        $value = $map->get($carrier, 'b');
        $this->assertSame('bravo', $value);
    }

    public function test_set_map_array_access_case(): void
    {
        $carrier = new ArrayObject(['A' => 'alpha']);
        $map = new ArrayAccessGetterSetter();

        $map->set($carrier, 'A', 'bravo');
        $this->assertCount(1, $carrier);
        $this->assertArrayHasKey('A', $carrier);
        $this->assertSame('bravo', $carrier['A']);

        $map->set($carrier, 'a', 'charlie');
        $this->assertCount(1, $carrier);
        $this->assertArrayHasKey('a', $carrier);
        $this->assertSame('charlie', $carrier['a']);
    }

    public function test_get_map_array_access_case(): void
    {
        $carrier = new ArrayObject(['A' => 'alpha']);
        $map = new ArrayAccessGetterSetter();

        $value = $map->get($carrier, 'A');
        $this->assertSame('alpha', $value);
    }

    public function test_set_unsupported_carrier(): void
    {
        $carrier = new stdClass();
        $map = new ArrayAccessGetterSetter();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported carrier type: ' . \get_class($carrier) . '. Unable to set value associated with key:a');
        $map->set($carrier, 'a', 'alpha');
    }

    public function test_set_empty_key(): void
    {
        $carrier = ['a' => 'alpha'];
        $map = new ArrayAccessGetterSetter();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to set value with an empty key');
        $map->set($carrier, '', 'alpha');
    }
}
