<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\API\Trace\AttributesInterface;
use OpenTelemetry\SDK\Trace\Event;
use OpenTelemetry\Tests\SDK\Util\TestClock;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    private const EVENT_NAME = 'test-event';
    private const ATTR_CONT = 2;
    private const DROPPED_ATTR_CONT = 1;

    private ?AttributesInterface $attributes = null;

    public function testGetName(): void
    {
        $this->assertSame(
            self::EVENT_NAME,
            $this->createEvent()->getName()
        );
    }

    public function testGetAttributes(): void
    {
        $this->assertSame(
            $this->getAttributesInterfaceMock(),
            $this->createEvent()->getAttributes()
        );
    }

    public function testGetEpochNanos(): void
    {
        $this->assertSame(
            $this->getClock()->now(),
            $this->createEvent()->getEpochNanos()
        );
    }

    public function testGetTotalAttributeCount(): void
    {
        $this->assertSame(
            self::ATTR_CONT,
            $this->createEvent()->getTotalAttributeCount()
        );
    }

    public function testGetDroppedAttributesCount(): void
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
