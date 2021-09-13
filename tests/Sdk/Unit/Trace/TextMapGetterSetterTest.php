<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use ArrayObject;
use InvalidArgumentException;
use OpenTelemetry\Sdk\Trace\TextMapGetterSetter;
use PHPUnit\Framework\TestCase;
use stdClass;

class TextMapGetterSetterTest extends TestCase
{
    public function testGetFromMapArray(): void
    {
        $carrier = ['a' => 'alpha'];
        $map = new TextMapGetterSetter();

        $this->assertSame('alpha', $map->get($carrier, 'a'));
        $this->assertSame(['a'], $map->keys($carrier));
    }

    public function testGetArrayValuesFromCarrier(): void
    {
        // Carrier contains an array as one of the values
        $carrier = [
            'a' => 'alpha',
            'b' => ['bravo'],
        ];
        $map = new TextMapGetterSetter();
        $this->assertSame('alpha', $map->get($carrier, 'a'));
        $this->assertSame('bravo', $map->get($carrier, 'b'));
        $this->assertSame(['a', 'b'], $map->keys($carrier));
    }

    public function testGetNumericalKeyFromCarrier(): void
    {
        // Carrier contains an array as one of the values
        $carrier = [
            1 => ['alpha'],
            'b' => 'bravo',
        ];
        $map = new TextMapGetterSetter();
        $this->assertNull($map->get($carrier, '1'));
        $this->assertSame('bravo', $map->get($carrier, 'b'));
        $this->assertSame([1, 'b'], $map->keys($carrier));
    }

    public function testGetFromMapArrayAccess(): void
    {
        $carrier = new ArrayObject(['a' => 'alpha']);
        $map = new TextMapGetterSetter();
        $this->expectException(InvalidArgumentException::class);
        $map->keys($carrier);
    }

    public function testGetFromUnsupportedCarrier(): void
    {
        $carrier = new stdClass();
        $map = new TextMapGetterSetter();
        $this->expectException(InvalidArgumentException::class);
        $map->get($carrier, 'a');
    }

    public function testKeysFromUnsupportedCarrier(): void
    {
        $carrier = new stdClass();
        $map = new TextMapGetterSetter();
        $this->expectException(InvalidArgumentException::class);
        $map->keys($carrier);
    }

    public function testInvalidGetFromMap(): void
    {
        $carrier = ['a' => 'alpha'];
        $map = new TextMapGetterSetter();

        $this->assertNull($map->get([], 'a'));
        $this->assertNull($map->get($carrier, 'b'));

        $this->expectException(InvalidArgumentException::class);
        $map->get('invalid carrier', 'a');
    }

    public function testSetMapArray(): void
    {
        $carrier = ['a' => 'alpha'];
        $map = new TextMapGetterSetter();

        $map->set($carrier, 'b', 'bravo');
        $value = $map->get($carrier, 'b');
        $this->assertSame('bravo', $value);
    }

    public function testSetMapArrayAccess(): void
    {
        $carrier = new ArrayObject(['a' => 'alpha']);
        $map = new TextMapGetterSetter();

        $map->set($carrier, 'b', 'bravo');
        $value = $map->get($carrier, 'b');
        $this->assertSame('bravo', $value);
    }

    public function testSetUnsupportedCarrier(): void
    {
        $carrier = new stdClass();
        $map = new TextMapGetterSetter();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid carrier of type ' . \get_class($carrier) . '. Unable to set value associated with key:a');
        $map->set($carrier, 'a', 'alpha');
    }

    public function testSetEmptyKey(): void
    {
        $carrier = ['a' => 'alpha'];
        $map = new TextMapGetterSetter();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to set value with an empty key');
        $map->set($carrier, '', 'alpha');
    }
}
