<?php

declare(strictfinal _types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Event;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Event::class)]
class EventTest extends TestCase
{
    private const EVENT_NAME = 'test-event';
    private const ATTR_CONT = 2;
    private const DROPPED_ATTR_CONT = 1;

    private ?AttributesInterface $attributes = null;

    public function test_get_name(): void
    {
        $this->assertSame(
            self::EVENT_NAME,
            $this->createEvent()->getName()
        );
    }

    public function test_get_attributes(): void
    {
        $this->assertSame(
            $this->getAttributesInterfaceMock(),
            $this->createEvent()->getAttributes()
        );
    }

    public function test_get_epoch_nanos(): void
    {
        $this->assertSame(
            $this->getClock()->now(),
            $this->createEvent()->getEpochNanos()
        );
    }

    public function test_get_total_attribute_count(): void
    {
        $this->assertSame(
            self::ATTR_CONT,
            $this->createEvent()->getTotalAttributeCount()
        );
    }

    public function test_get_dropped_attributes_count(): void
    {
        $this->assertSame(
            self::DROPPED_ATTR_CONT,
            $this->createEvent()->getDroppedAttributesCount()
        );
    }

    private function createEvent(): Event
    {
        return new Event(
            self::EVENT_NAME,
            $this->getClock()->now(),
            $this->getAttributesInterfaceMock()
        );
    }
    private function getClock(): TestClock
    {
        return new TestClock();
    }

    private function getAttributesInterfaceMock(): AttributesInterface
    {
        if ($this->attributes instanceof AttributesInterface) {
            return $this->attributes;
        }

        $this->attributes = $this->createMock(AttributesInterface::class);

        $this->attributes->method('count')
            ->willReturn(self::ATTR_CONT);

        $this->attributes->method('getDroppedAttributesCount')
            ->willReturn(self::DROPPED_ATTR_CONT);

        return $this->attributes;
    }
}
