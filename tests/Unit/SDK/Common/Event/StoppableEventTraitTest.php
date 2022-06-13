<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Event;

use OpenTelemetry\SDK\Common\Event\StoppableEventTrait;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * @covers \OpenTelemetry\SDK\Common\Event\StoppableEventTrait
 */
class StoppableEventTraitTest extends TestCase
{
    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function test_stop_event(): void
    {
        $event = $this->getEvent();
        $this->assertFalse($event->isPropagationStopped());
        $event->stopPropagation(); //@phpstan-ignore-line
        $this->assertTrue($event->isPropagationStopped());
    }

    public function getEvent(): StoppableEventInterface
    {
        return new class() implements StoppableEventInterface {
            use StoppableEventTrait;
        };
    }
}
