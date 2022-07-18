<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Event;

use CloudEvents\V1\CloudEventInterface;
use OpenTelemetry\API\Common\Event\Dispatcher;
use OpenTelemetry\API\Common\Event\EmitsEventsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Common\Event\EmitsEventsTrait
 */
class EmitsEventsTraitTest extends TestCase
{
    public function test_emits_event(): void
    {
        $event = $this->createMock(CloudEventInterface::class);
        $event->method('getType')->willReturn('bar');
        $called = false;
        $class = $this->createInstance();
        Dispatcher::getInstance()->listen($event->getType(), function () use (&$called) {
            $this->assertTrue(true, 'listener was called');
            $called = true;
        });
        $class->run('emit', $event);
        $this->assertTrue($called);
    }

    private function createInstance(): object
    {
        return new class() {
            use EmitsEventsTrait;
            //accessor for protected trait methods
            public function run(string $method, $param): void
            {
                $this->{$method}($param);
            }
        };
    }
}
